<?php

namespace App\Jobs;

use App\Models\WechatBot;
use App\Models\WechatContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WechatSendQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $to;
    public $content;
    public $wechatBot;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        WechatBot $wechatBot,
        WechatContent $content,
        $to)
    {
        $this->to = $to;
        $this->content = $content;
        $this->wechatBot = $wechatBot;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!$this->wechatBot->send($this->to, $this->content)) $this->fail();
    }
}
