<?php

namespace App\Console\Commands;

use App\Models\WechatBot;
use App\Models\WechatContent;
use App\Services\Wechat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WechatSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wechat:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Contact and tags from wechat';

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
        WechatBot::active()->each(function(WechatBot $wechatBot){
            $wechat = new Wechat($wechatBot->wxid);
            $response = $wechat->isOnline();
            if($response->ok() && $response['code'] == 10001){
                $wechatBot->update(['login_at'=> null]);
                Log::info(__METHOD__, ['已下线', $wechatBot->nickName]);
            }else{
                // Log::debug(__METHOD__, [$wechatBot->nickName, '上线时间', $wechatBot->login_at ]);
                $wechatBot->sync();
                $wechatBot->send(['filehelper'], WechatContent::make([
                    'name' => 'tmp',
                    'type' => WechatContent::TYPE_TEXT,
                    'content' => ['content'=> "同步联系人和标签完成"]
                ]));
            }
        });

        return 0;
    }
}
