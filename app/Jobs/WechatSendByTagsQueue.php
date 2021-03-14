<?php

namespace App\Jobs;

use App\Models\WechatBot;
use App\Models\WechatBotContact;
use App\Models\WechatContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WechatSendByTagsQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $selectedTags;
    public $tagWith;
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
        $selectedTags,
        $tagWith)
    {
        $this->selectedTags = $selectedTags;
        $this->tagWith = $tagWith;
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
        $tos = WechatBotContact::with('contact')
            ->withAnyTags($this->selectedTags, $this->tagWith)
            ->get()
            ->pluck('contact.userName');
        $this->wechatBot->send($tos, $this->content);
    }
}
