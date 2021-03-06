<?php

namespace App\Console\Commands;

use App\Models\WechatBot;
use App\Models\WechatContent;
use App\Services\Wechat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WechatIsLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wechat:islive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check wechat is live';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        WechatBot::all()->each(function(WechatBot $wechatBot){
            // $wechat = new Wechat($wechatBot->wxid);
            $response = $wechatBot->wechat->who();
            if($response->ok() && $response['code'] == 10001){
                $wechatBot->update(['login_at'=> null]);
                Log::info(__METHOD__, ['已下线', $wechatBot->nickName]);
            }else{
                if(!$wechatBot->login_at){
                    $wechatBot->update(['login_at'=> now()]);
                }
                $hours = $wechatBot->login_at->diffInHours(now());
                $wechatBot->sendTo(['filehelper', '17381631579@chatroom'], WechatContent::make([
                    'name' => 'islive',
                    'type' => WechatContent::TYPE_TEXT,
                    'content' => ['content'=> "已上线{$hours}小时"]
                ]));

                Log::info(__METHOD__, [$wechatBot->nickName, $wechatBot->login_at ]);
            }
        });

        return 0;
    }
}
