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
use App\Models\WechatContact;
use Illuminate\Support\Arr;

// Init login of a wechatBot
class InitWechat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private String $wId;
    private Team $team;
    public $timeout = 300; // 5分钟 php artisan queue:listen --timeout=300

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($wId, Team $team)
    {
        $this->wId = $wId;
        $this->team = $team;
        Log::info(__METHOD__, ["ScanWechatQR: A Job dispatch :", $wId]);
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
            // 二次刷新时，cache的二维码 wId 会变，利用这个终止 之前的Job
            if($this->wId != Cache::get('weiju_wId')) $loop = false;

            $response = $wechat->login();
            // "{\"code\":-8,\"msg\":\"异常：扫码状态返回的交互key不存在\",\"data\":{}}"}]
            // "{\"code\":10001,\"msg\":\"微信未扫码\",\"data\":{}}"]
            if($response->ok() && $response['code'] == 1000) {
                $Wxid = $response['data']['wcId'];
                $loop = false;
                Log::info(__METHOD__, ["ScanWechatQR: login success", $this->wId, $Wxid]);
                
                // # 保存/更新 bot 信息
                $wechat = new Wechat($Wxid);
                $response = $wechat->who();
                if($response->ok() && $response['code'] == 1000){
                    $wechatBotData = $response['data'];
                    // 不准 切换Bot 绑定！
                    if($bot = $this->team->bot){ // 当前Team存在 一个bot了
                        Log::debug(__METHOD__, ["初始化 Team ：已绑定！"]);
                        if($bot->userName != $wechatBotData['userName']){
                            Log::error(__METHOD__, ["登录的bot与先前的绑定微信ID不匹配！"]);
                            $wechat->sendText('helper', '登录的bot与先前的绑定微信ID不符, 即将自动退出，请10分钟后再试！', $response['data']['userName']);
                            $wechat->logout();
                            
                            $loop = false;
                            break;
                        }
                    }else{ //当前Team还没有绑定bot
                        Log::debug(__METHOD__, ["初始化 Team 成功！"]);
                        $wechatBotData['team_id'] = $this->team->id;
                    }

                    $wechatBot = WechatBot::updateOrCreate(['userName' => $wechatBotData['userName']], Arr::except($wechatBotData, 'userName'));  // @see https://laravel.com/docs/8.x/eloquent#upserts
                }else{
                    Log::error(__METHOD__, [__LINE__, $response]);
                }
                
                // InitWechat::dispatch($Wxid); // 500联系人init()需要2分钟 
                Log::debug("InitWechat", ["begin"]);
                // $wechat->init(); //为什么要init，不init可以用吗？
                Log::debug("InitWechat", ["done"]);

                # 保存 bot 的通讯录信息（不含群成员）
                $response = $wechat->getAllContacts();
                if($response->ok() && $response['code'] == 1000){
                    Log::info(__METHOD__, ["Init WechatContact", "begin"]);
                    $attachs = [];
                    $teamOwnerId = $this->team->owner->id;
                    foreach ($response['data'] as $type => $values) {
                        foreach ($values as $data) {
                            $contact = WechatContact::firstWhere('userName', $data['userName']);
                            if(!$contact) {
                                $contact = WechatContact::create($data);
                                $attachs[$contact->id] = [
                                    'remark' => isset($data['remark'])?$data['remark']:'',
                                    'type' => WechatContact::TYPES[$type], // 0:friend好友,1:group群,2:public公众号,3:非好友群成员
                                    'seat_user_id' => $teamOwnerId,
                                ];// @see https://laravel.com/docs/8.x/eloquent-relationships#updating-many-to-many-relationships
                            }else{
                                // 更新资料
                                $contact->update($data);
                            }
                        }
                        Log::info(__METHOD__, ["Init WechatContact $type ", count($values)]);
                    }
                    $wechatBot->contacts()->sync($attachs);
                }else{
                    Log::error(__METHOD__, [__LINE__, $response]);
                }
                
                $response = $wechat->setCallBackUrl(); //TODO 用户自定义CallBackUrl webhook
                if($response->ok() && $response['code'] == 1){
                    Log::debug(__METHOD__, ["setCallBackUrl"," 成功"]);
                }else{
                    Log::debug(__METHOD__, ["setCallBackUrl"," 失败", $response]);
                }

                $loop = false;
                break;
            }else{
                Log::error(__METHOD__, [__LINE__, $this->wId, $response]);
            }
            sleep(3);
            $loopCount++;
        }
    }
}