<?php

namespace App\Http\Controllers;

use App\Models\WechatBot;
use App\Models\WechatContact;
use App\Models\WechatContent;
use App\Models\WechatMessage;
use App\Services\Tuling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class WeijuController extends Controller
{

    public function test(Request $request){
        info($request->all());
        return true;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listen(Request $request)
    {
        $wechatMessage = $request['message']['data'];

        // 企业微信账号：  25984983457841966@openim
        // 企业微信群
        // fromUser=sendUser=9223372041442168057@im.chatroom
        // content 
        // "content":"bluesky_still:\ngooood"
        // "content":"bluesky_still:\n<?xml
        if(Str::endsWith($wechatMessage['fromUser'], '@im.chatroom') || Str::endsWith($wechatMessage['fromUser'], '@openim')){
            return Log::debug(__METHOD__, ['企业微信群消息', '暂不处理']);
        }
        
        // 消息类型 type 不存储
            // 1. 好友 发消息给 bot
            // 2. 群成员 发消息给 群
            // 0 == self
                // 0.1 bot 主动发消息/被动回复 到 好友
                // 0.2 bot 主动发消息/被动响应 到 群
            // 3 bot 主动发消息 给公众号
            // 4 被动收到公众号消息
        $isGh = (Str::startsWith($wechatMessage['toUser'], 'gh_') || Str::startsWith($wechatMessage['sendUser'], 'gh_'))?:false; // "toUser":"gh_c213xxx7da3"   // isToGh isFromGh
        
        // 处理消息，获取发送者和接收者（bot的Wxid）
        if($wechatMessage['self'] == false){ // 1/2：别人发消息 
            $wechatMessage['wechat_bot_id'] = $wechatMessage['toUser'];
            if($wechatMessage['sendUser'] == $wechatMessage['fromUser']){
                $wechatMessage['type'] = 1;
                $wechatMessage['conversation'] = $wechatMessage['sendUser'];
                $wechatMessage['from_contact_id'] =  $wechatMessage['sendUser'];
            }else{
                $wechatMessage['type'] = 2;
                $wechatMessage['conversation'] = $wechatMessage['fromUser'];
                $wechatMessage['from_contact_id'] = $wechatMessage['sendUser'];
            }
            if($isGh){
                $wechatMessage['type'] = 4;
            }
        }else{ // self：true 主动发送消息 时，from_contact_id == null
            $wechatMessage['type'] = 0;
            $wechatMessage['wechat_bot_id'] = $wechatMessage['sendUser'];
            $wechatMessage['conversation'] = $wechatMessage['toUser'];
            if($isGh){
                $wechatMessage['type'] = 3;
            }
        }

        // 把 string 转换成 id 表关系 foreignId
        $wechatBot = WechatBot::with('meta')->firstWhere('userName', $wechatMessage['wechat_bot_id']);
        // 默认不接收公众号的消息
            // @see $wechatBot->setMeta('wechatListenGh', 0); 
            // (boolean) ($allMetaCollection['wechatListenGh']??false)
        if($isGh && !$wechatBot->getMeta('wechatListenGh')) return;

        // 默认不接收群消息，但可以有选择的接收群消息 
        // @see 
            // $wechatBot->setMeta('wechatListenRoom', 0); 
            // $wechatBot->setMeta('wechatListenRoomAll', 1);  // 接收所有
            // $wechatBot->setMeta('wechatListenRooms', ['5829025039@chatroom']); // 只接收少数几个群的群消息
        $isFromRoom = $wechatMessage['category']; // int 0:私聊消息;1:群组消息
        if($isFromRoom){
            if(!$wechatBot->getMeta('wechatListenRoom')) { // 默认不接收群消息
                Log::debug(__METHOD__, ['没有开启接收群消息']);
                return;
            }else{  // 只接收少数几个群的群消息
                if(!($wechatBot->getMeta('wechatListenRoomAll') 
                    || in_array($wechatMessage['conversation'], (array) $wechatBot->getMeta('wechatListenRooms')))) {
                    Log::debug(__METHOD__, ['没有开启接收指定/全部群消息']);
                    return;
                } 
            }
            
            // 群里的某个非好友 成员 发言（处理之前初始化并没有保存为contact的情况）
            $contact = WechatContact::firstWhere('userName', $wechatMessage['from_contact_id']);
            if(!$contact) {
                $wxid = $wechatMessage['from_contact_id'];
                $contact = $wechatBot->addOrUpdateContact($wxid, WechatContact::TYPES['stranger']);// type=3
            }
            $wechatMessage['from_contact_id'] = $contact->id;
        }

        // 处理时间戳 // 2021-02-18 = 1613540658
        // 更新时间，设置为 返回消息中的时间, 以后使用 orderBy('updated_at')顺序
        $wechatMessage['updated_at'] = $wechatMessage['timestamp'];

        // 处理 <?xml  <msg
            // 0:简单文本消息 
            // 3:音频：点击▶️收听
        if(Str::startsWith($wechatMessage['content'], '<?xml ')) {
            $appmsgType = "init";
            $msg = xStringToArray($wechatMessage['content']);
            if(Arr::has($msg, 'appmsg.type')){
                $appmsgType = $msg['appmsg']['type'];
                switch ($appmsgType) {
                    case '3':
                        Log::debug(__METHOD__, ['XML消息', '音频：点击▶️收听', $msg['appmsg']['title'], $msg['appmsg']['url']]);
                        # TODO code...// appmsg.type = 3;
                        break;
                    case '33':
                        Log::debug(__METHOD__, ['XML消息', '小程序', $msg['appmsg']['title'],$msg['appmsg']['sourcedisplayname']]);
                        # TODO code...// appmsg.type = 33;
                        break;
                    
                    default:
                        Log::error(__METHOD__, ['XML消息', '未处理', $appmsgType]);
                        break;
                }
            }else{
                Log::debug(__METHOD__, ['XML消息', "待处理", $request['message']]);
            }
        }elseif(Str::startsWith($wechatMessage['content'], '<msg')){
            $msg = xStringToArray($wechatMessage['content']);
            switch ($wechatMessage['msgType']) {
                case '37':
                    // $msg['@attributes'] 字段
                        // bigheadimgurl 
                        // smallheadimgurl
                        // encryptusername v3_xxx@stranger
                        // ticket v4_xxx@stranger
                    $v1 = $msg['@attributes']['encryptusername'];
                    $v2 = $msg['@attributes']['ticket'];
                    $wechatBot->friendAgree($v1, $v2, $msg['@attributes']['fromusername']);

                    Log::debug(__METHOD__, ['好友请求', $msg['@attributes']['fromnickname'], $msg['@attributes']['content']]);
                    break;
                default:
                    Log::debug(__METHOD__, ['<msg消息', "待处理", $request['message']]);
                    break;
            }

            // else if(Arr::has($msg, 'img')){ //以<msg> <img 开头 
            //     $appmsgType = 'image';
            //     $md5 = $msg['img']['@attributes']['md5'];
            //     $size = $msg['img']['@attributes']['length'];
            //     // TODO 如果已经下载了，不再下载，引用之前的链接文件！
            //     Log::debug(__METHOD__, ['接收到图片', $md5, $size]);
            // }else{
            //     Log::debug(__METHOD__, ["待处理复杂XML消息", $request['message']]);
            // }
        }else{ // 简单消息
            Log::debug(__METHOD__, ['简单消息', 'or待处理', $request['message']]);
            // "msgType":10000, "content":"你已添加了天空蔚蓝，现在可以开始聊天了。"
            // "msgType":10000, "content":"天空蔚蓝开启了朋友验证，你还不是他（她）朋友。请先发送朋友验证请求，对方验证通过后，才能聊天。<a href=\"weixin://findfriend/verifycontact\">发送朋友验证</a>
        }

        // 收到公众号消息，没有 from_contact_id
            // {
            //     "messageType": 2,
            //     "category": 0,
            //     "content": "620",
            //     "fromUser": "wxid_7nof1pauaqyd22",
            //     "msgId": 1720340307,
            //     "self": true,
            //     "timestamp": 1613825861,
            //     "toUser": "gh_c2138e687da3",
            //     "msgType": 1,
            //     "type": 0,
            //     "atlist": [],
            //     "sendUser": "wxid_7nof1pauaqyd22",
            //     "wechat_bot_id": "wxid_7nof1pauaqyd22",
            //     "conversation": "gh_c2138e687da3",
            //     "updated_at": 1613825861
            //   }
        
        $wechatMessage['wechat_bot_id'] = $wechatBot->id;
        $conversation = WechatContact::firstWhere('userName', $wechatMessage['conversation']);

        // // 企业微信群
        //     // fromUser=sendUser=9223372041442168057@im.chatroom
        //     // content 
        //         // "content":"bluesky_still:\ngooood"
        //         // "content":"bluesky_still:\n<?xml
        // if(Str::endsWith($wechatMessage['fromUser'], '@im.chatroom')){
        //     $from = explode(':', $wechatMessage['content']);
        //     $wechatMessage['from_contact_id'] = $from[0];
        //     $wechatMessage['content'] = str_replace("{$from[0]}:\n",'', $wechatMessage['content']);
        // }
        

        // 保存新群： 突然被拉到一个新群里！
            // 'public'=>0, // 0
            // 'friend'=>1, // 1
            // 'group'=>2, // 2
            // 'stranger'=>3, // 3
        if($conversation){
            $wechatMessage['conversation'] = $conversation->id;
        }else{
            // 保存群

            // if(Str::endsWith($wechatMessage['fromUser'], '@im.chatroom')){
            //     // 企业微信群
            //     $wxid = $wechatMessage['sendUser'];
            // }

            $wxid = $wechatMessage['conversation'];
            $contact = $wechatBot->addOrUpdateContact($wxid, WechatContact::TYPES['group']);//2
            $wechatMessage['conversation'] = $contact->id;
        }
        // 为什么1条信息，数据库中有2个记录，同样的msgId？
            // server重发 发送2次post
            // 使用Cache 缓存要处理的条目
        // TODO 加入写入队列，使用队列写入数据库?
            
        $rawContent = $wechatMessage['content']; //keep rawContent in $content
        $needSave = false;
        // TODO 49 点击▶️收听， 不需要下载 !in_array($appmsgType,[3,33])
        if(in_array($wechatMessage['msgType'], WechatMessage::ATTACHMENY_MSG_TYPES) && !in_array($appmsgType,[3,33])){
            $needSave = true;
            // 下载 文件更新 content为链接
            $response = $wechatBot->wechat->saveAttachmentResponse($wechatMessage['msgType'], $wechatMessage['msgId'], $wechatMessage['fromUser'], $wechatMessage['content']);
            
            if($response->ok() && $response->json('code') === 1000){
                $data = str_replace('http:', 'https:', $response->json('data'));
                $wechatMessage['content'] = compact('data');
                // TODO 下载到本地，给出md5
                // $str = md5(Storage::get('wechat/87035.jpg')); //3d1e734982e6c18a65c88dd34eac4d96
                // $str = Storage::size('wechat/87035.jpg');
            }else{ //TODO Failed Retry, 队列天然支持
                Log::error(__METHOD__, ['文件消息下载失败，请使用saveAttachmentBy(WechatMessage)重试！', $wechatMessage, $response]);
            }
        }
        // 处理纯文本消息json
            // "msgType":1, "content":"nihao"    
            // "msgType":10000, "content":"你已添加了天空蔚蓝，现在可以开始聊天了。"
        if(in_array($wechatMessage['msgType'], WechatMessage::MSG_TYPES_SIMPLE)){
            $wechatMessage['content'] = ['content'=>$rawContent];
        }
        WechatMessage::create($wechatMessage);

        // For debug in local
        if(env('local') && $needSave) Storage::put("wechat/message.".$msg->id.".rawcontent", $rawContent);
        
        //  1.响应内容为文本 2.开启了autoreply  // 只响应第一个匹配的
        // TODO check 且不是群
        if($wechatBot->getMeta('wechatAutoReply', false) && $wechatMessage['msgType'] == WechatMessage::MSG_TYPES['text']) {
            $keywords = $wechatBot->autoReplies()->pluck('keyword','wechat_content_id');
            $to = [$wechatMessage['sendUser']];
            foreach ($keywords as $id => $keyword) {
                if(Str::is($keyword, $rawContent)){
                    // TODO preg;
                    // @see https://laravel.com/docs/8.x/helpers#method-str-is
                    return $wechatBot->send($to, WechatContent::find($id));//send(Array $contacts, WechatContent $wchatContent)
                }
            }

            // Tuling
            if(!$wechatBot->getMeta('wechatTuingReply', false)) return;
            $tuling = new Tuling();
            $response = $tuling->post($rawContent);
            if(!$response->ok()) return;
            // 优先回复文本
            foreach ($response['results'] as $result) {
                if($result['resultType'] == 'text'){
                    return $wechatBot->send($to, WechatContent::make([
                        'name' => 'tuling tmp',
                        'type' => WechatContent::TYPE_TEXT,
                        'content' => ['data'=>['content'=>$result['values']['text']]]
                    ]));
                }
            }
        }
    }
}
