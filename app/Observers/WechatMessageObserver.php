<?php

namespace App\Observers;

use App\Models\WechatContent;
use App\Models\WechatMessage;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\WebhookCall;

class WechatMessageObserver
{
    /**
     * Handle the WechatMessage "created" event.
     *
     * @param  \App\Models\WechatMessage  $wechatMessage
     * @return void
     */
    public function created(WechatMessage $wechatMessage)
    {
        // $wechatMessage = WechatMessage::with('contact')->find($wechatMessage->id);
        $wechatMessage->contact; // load ::with('contact')

        // type 类型,需要转化成 文本？
        // seat_user_id 主动发送
        // contact 好友
        // content json data

        $wechatBot = $wechatMessage->wechatBot->load('meta');
        
        $wechatWebhook = $wechatBot->getMeta('wechatWebhook', false);
        $wechatWebhookUrl = $wechatBot->getMeta('wechatWebhookUrl', false);
        $wechatWebhookSecret = $wechatBot->getMeta('wechatWebhookSecret', false);

        if($wechatWebhook && $wechatWebhookUrl && $wechatWebhookSecret){
            $data = collect($wechatMessage)->except(['wechat_bot', 'conversation', 'wechat_bot_id', 'msgId', 'deleted_at', 'updated_at', 'contact.deleted_at', 'contact.updated_at'])->toArray();
            $data['type'] = WechatContent::TYPES[$data['type']];
            WebhookCall::create()
                ->url($wechatWebhookUrl)
                ->payload($data)
                ->useSecret($wechatWebhookSecret)
                ->dispatchNow();

            Log::debug(__METHOD__, ['WebhookCall', $wechatBot->userName, $wechatWebhookUrl]);
        }
    }
}
