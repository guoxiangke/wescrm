<?php

namespace App\Jobs;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Wechat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\WechatBot;
use App\Models\WechatBotContact;
use App\Models\WechatContact;
use Illuminate\Support\Arr;

// Init login of a wechatBot
class WechatInitQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private String $wId;
    private Team $team;
    public $tries = 2;
    public $timeout = 900; // 15分钟 php artisan queue:listen --timeout=300
    private String $terminateKey; // 一旦进入初始化，不要终止！ // 和当前操作 微信登录的用户ID 相关！

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($wId, Team $team, $userId)
    {
        $this->wId = $wId;
        $this->team = $team;
        Log::info(__METHOD__, ["ScanWechatQR: A Job dispatch :", $wId]);

        // 一旦进入初始化，不要终止！ // 和当前操作 微信登录的用户ID 相关！
        $this->terminateKey = 'weixin.processing.'.$userId;
        Cache::put($this->terminateKey, true, now()->addMinutes(5));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $wechat = new Wechat("no_Wxid_yet", $this->wId);
        $loop = true;
        $loopCount = 0;
        while($loop && $loopCount<15){
            // 二次刷新时，cache的二维码 wId 会变，利用这个终止 之前的Job && // 一旦进入初始化，不要终止！
            if($this->wId != Cache::get('weiju_wId') && Cache::get($this->terminateKey)) $loop = false;

            $response = $wechat->login();
            // "{\"code\":-8,\"msg\":\"异常：扫码状态返回的交互key不存在\",\"data\":{}}"}]
            // "{\"code\":10001,\"msg\":\"微信未扫码\",\"data\":{}}"]
            if($response->ok() && $response['code'] == 1000) {
                // 一旦进入初始化，不要终止！
                Cache::put($this->terminateKey, false, now()->addMinutes(5));

                $Wxid = $response['data']['wcId'];
                $loop = false;
                Log::info("InitWechat: ScanWechatQR success {$Wxid}");
                
                // # 保存/更新 bot 信息
                $wechat = new Wechat($Wxid);
                $response = $wechat->who();
                if($response->ok() && $response['code'] == 1000){
                    $wechatBotData = $response['data'];
                    // 不准 切换Bot 绑定！
                    if($bot = $this->team->bot){ // 当前Team存在 一个bot了
                        Log::info("Init Wechat: {$wechatBotData['userName']} Team ：已绑定");
                        if($bot->userName != $wechatBotData['userName']){
                            Log::error(__METHOD__, ["InitWechat: 登录的bot与先前的绑定微信ID不匹配！"]);
                            $wechat->send("sendText", ['ToWxid'=>'filehelper', 'content'=>'登录的bot与先前的绑定微信ID不符, 即将自动退出，请使用已绑定的微信登录！']);
                            $wechat->logout();
                            
                            $loop = false;
                            break;
                        }
                    }else{ //当前Team还没有绑定bot
                        Log::debug(__METHOD__, ["InitWechat: 初始化 Team 成功！"]);
                        $wechatBotData['team_id'] = $this->team->id;
                    }
                    $updateData = array_merge(['login_at'=>now()], Arr::except($wechatBotData, 'userName'));
                    $wechatBot = WechatBot::updateOrCreate(['userName' => $wechatBotData['userName']], $updateData);  // @see https://laravel.com/docs/8.x/eloquent#upserts
                    Log::info("InitWechat: 登录成功  {$wechatBotData['userName']} ");
                }else{
                    Log::error(__METHOD__, [__LINE__, $response]);
                }
                
                $wechatBot->sync();
                // $wechat->init(); //为什么要init，不init可以用吗？

                $response = $wechat->setCallBackUrl();
                if($response->ok() && $response['code'] == 1){
                    Log::debug(__METHOD__, ["setCallBackUrl"," 成功"]);
                }else{
                    Log::debug(__METHOD__, ["setCallBackUrl"," 失败", $response->json()]);
                }

                // 更新在线客户端数量
                
                $loop = false;
                break;
            }else{
                Log::error(__METHOD__, [__LINE__, $this->wId, $response->json()]);
            }
            sleep(3);
            $loopCount++;
        }
    }
}