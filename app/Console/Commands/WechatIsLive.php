<?php

namespace App\Console\Commands;

use App\Models\WechatBot;
use App\Services\Wechat;
use App\Services\Weiju;
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
    public function handle(Weiju $weiju)
    {
        WechatBot::active()->each(function(WechatBot $wechatBot){
            $wechat = new Wechat($wechatBot->wxid);
            $response = $wechat->isOnline();
            if($response->ok() && $response['code'] == 10001){
                $wechatBot->update(['login_at'=> null]);
                Log::info(__METHOD__, ['已下线', $wechatBot->nickName]);
            }else{
                Log::debug(__METHOD__, [$wechatBot->nickName, '上线时间', $wechatBot->login_at ]);
            }
        });

        return 0;
    }
}
