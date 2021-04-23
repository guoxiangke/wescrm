<?php

namespace App\Jobs;

use App\Models\WechatBot;
use App\Models\WechatContact;
use App\Models\WechatContent;
use App\Services\Wechat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WechatAgreeQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $v1;
    private $v2;

    private $wechatBot;
    private $wxid;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($v1, $v2, WechatBot $wechatBot, $wxid)
    {
        $this->v1 = $v1;
        $this->v2 = $v2;

        $this->wechatBot = $wechatBot;
        $this->wxid = $wxid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 在同意之前，保存一下好友通讯录
        $this->wechatBot->addOrUpdateContact($this->wxid, WechatContact::TYPES['friend']);

        $this->wechatBot->wechat->friendAgree($this->v1, $this->v2);

        //自动回复欢迎语
        $welcomeMsg = $this->wechatBot->getMeta('wechatWeclomeMsg', '你好');
        $this->wechatBot->send($this->wxid, WechatContent::make([
            'name' => 'auto agree tmp',
            'type' => WechatContent::TYPE_TEXT,
            'content' => ['content'=>$welcomeMsg]
        ]));
    }
}
