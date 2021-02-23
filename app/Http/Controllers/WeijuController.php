<?php

namespace App\Http\Controllers;

use App\Models\WechatBot;
use App\Models\WechatContact;
use App\Models\WechatMessage;
use App\Services\Wechat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class WeijuController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listen(Request $request)
    {
        $inputs = $request->toArray();

        // TODO check $table->text('msgSource')->nullable();// ！message.data.msgSource	String	消息源
        // TODO check $table->text('pushContent')->nullable();// message.data.pushContent	String	谁艾特了你
        if(isset($inputs['message']['data']['msgSource']) || isset($inputs['message']['data']['pushContent']))
            Log::error(__METHOD__, ['TODO Callback原始消息中含msgSource、pushContent', $inputs]);
            
        $messageType = $inputs['message']['messageType'];
        $wechatMessage = array_merge(compact('messageType'), $inputs['message']['data']);

        // 企业微信账号：  25984983457841966@openim
        // 企业微信群
        // fromUser=sendUser=9223372041442168057@im.chatroom
        // content 
        // "content":"bluesky_still:\ngooood"
        // "content":"bluesky_still:\n<?xml
        if(Str::endsWith($wechatMessage['fromUser'], '@im.chatroom') || Str::endsWith($wechatMessage['fromUser'], '@openim')){
            Log::info(__METHOD__, ['收到企业微信群消息','暂不处理']);
            return;
        }
        
        // 消息类型 type
            // 1. 好友 发消息给 bot
            // 2. 群成员 发消息给 群
            // 0 == self
                // 0.1 bot 主动发消息/被动回复 到 好友
                // 0.2 bot 主动发消息/被动响应 到 群
            // 3 bot 主动发消息 给公众号
            // 4 被动收到公众号消息
        $isGh = (Str::startsWith($wechatMessage['toUser'], 'gh_') || Str::startsWith($wechatMessage['sendUser'], 'gh_'))?:false; // "toUser":"gh_c213xxx7da3"   // isToGh isFromGh
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
        $Wxid = $wechatMessage['wechat_bot_id'];
        $wechatBot = WechatBot::firstWhere('userName', $Wxid);

        // 默认不接收公众号的消息
            // @see $wechatBot->setMeta('is_listen_msg_gh', 0); 
        if(!$wechatBot->getMeta('is_listen_msg_gh')) return;

        // 默认不接收群消息，但可以有选择的接收群消息 
        // @see 
            // $wechatBot->setMeta('is_listen_msg_room', 0); 
            // $wechatBot->setMeta('listen_msg_rooms', ['5829025039@chatroom']); // 只接收少数几个群的群消息
        $isRoom = $wechatMessage['category']; // int 0:私聊消息;1:群组消息
        if($isRoom){
            if(!$wechatBot->getMeta('is_listen_msg_room')) { // 默认不接收群消息
                return;
            }else{  // 只接收少数几个群的群消息
                $roomArray = (array) $wechatBot->getMeta('listen_msg_rooms');
                if(!in_array($wechatMessage['conversation'], $roomArray)) return;
            }
        }

        // 处理时间戳 // 2021-02-18 = 1613540658
        // 更新时间，设置为 返回消息中的时间, 以后使用 orderBy('updated_at')顺序
        $wechatMessage['updated_at'] = $wechatMessage['timestamp'];
        // $table->boolean('category')->default(false)->comment('int 0:私聊消息;1:群组消息');

        // 处理 <?xml  <msg
        $xmlType = 0;
            // 0:简单文本消息 
            // 3:音频：点击▶️收听
        $appmsgType = "init";
        if(Str::startsWith($wechatMessage['content'], ['<?xml ','<msg'])) {
            $msg = xStringToArray($wechatMessage['content']);
            if(Arr::has($msg,'appmsg.type')){ // '<?xml '
                $appmsgType = $msg['appmsg']['type']; //xmlType
                switch ($appmsgType) {
                    case '3':
                        Log::debug(__METHOD__, ['收到 音频：点击▶️收听', $msg['appmsg']['title'], $msg['appmsg']['url']]);
                        # TODO code...// appmsg.type = 3;
                        break;
                    case '33':
                        Log::debug(__METHOD__, ['收到 小程序',$msg['appmsg']['title'],$msg['appmsg']['sourcedisplayname']]);
                        # TODO code...// appmsg.type = 33;
                        break;
                    
                    default:
                        Log::error(__METHOD__, ["收到 未处理 $appmsgType 消息"]);
                        break;
                }
            }else if(Arr::has($msg, 'img')){ //以<msg> <img 开头 
                $appmsgType = 'image';
                $md5 = $msg['img']['@attributes']['md5'];
                $size = $msg['img']['@attributes']['length'];
                // TODO 如果已经下载了，不再下载，引用之前的链接文件！
                Log::debug(__METHOD__, ['接收到图片', $md5, $size]);
            }else{
                Log::debug(__METHOD__, ["待处理复杂消息 $messageType", Arr::except($wechatMessage, ['content', 'img'])]);
            }
        }else{ // 简单消息
            Log::debug(__METHOD__, ['待处理简单消息', $wechatMessage]);
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

        // 企业微信群
            // fromUser=sendUser=9223372041442168057@im.chatroom
            // content 
                // "content":"bluesky_still:\ngooood"
                // "content":"bluesky_still:\n<?xml
        if(Str::endsWith($wechatMessage['fromUser'], '@im.chatroom')){
            $from = explode(':', $wechatMessage['content']);
            $wechatMessage['from_contact_id'] = $from[0];
            $wechatMessage['content'] = str_replace("{$from[0]}:\n",'', $wechatMessage['content']);
        }
        

        // 保存新群： 突然被拉到一个新群里！
        if($conversation){
            $wechatMessage['conversation'] = $conversation->id;
        }else{
            if(Str::endsWith($wechatMessage['fromUser'], '@im.chatroom')){
                // 企业微信群
                $who = $wechatMessage['sendUser'];
            }else{
                $who = $wechatMessage['from_contact_id'];
            }
            $contact = $this->save_contact($who, $Wxid, $wechatBot);
            $wechatMessage['conversation'] = $contact->id;
        }

       
        // 群里的某个非好友 成员 发言（处理之前初始化并没有保存为contact的情况）
        if(isset($wechatMessage['from_contact_id'])){
            // 处理群成员（非好友）的信息，先保存为contact
            $contact = WechatContact::firstWhere('userName', $wechatMessage['from_contact_id']);
            if(!$contact) {
                $who = $wechatMessage['from_contact_id'];
                $contact = $this->save_contact($who, $Wxid, $wechatBot);
            }
            $wechatMessage['from_contact_id'] = $contact->id;
        }

        // 为什么1条信息，数据库中有2个记录，同样的msgId？
            // server重发 发送2次post
            // 使用Cache 缓存要处理的条目
        // TODO 加入写入队列，使用队列写入数据库?
            
        $rawContent = $wechatMessage['content'];
        $needSave = false;
        // TODO 49 点击▶️收听， 不需要下载 !in_array($appmsgType,[3,33])
        if(in_array($wechatMessage['msgType'], WechatMessage::ATTACHMENY_MSG_TYPES) && !in_array($appmsgType,[3,33])){
            $needSave = true;

            // 下载 文件更新 content为链接
            $Wxid = $wechatMessage['toUser']; //$inputs['message']['data']['toUser'];
            $wechat = new Wechat($Wxid);
            $response = $wechat->saveAttachmentResponse($wechatMessage['msgType'], $wechatMessage['msgId'], $wechatMessage['fromUser'], $wechatMessage['content']);
            
            if($response->ok() && $response->json('code') === 1000){
                $wechatMessage['content'] = str_replace('http:', 'https:', $response->json('data'));
                // TODO 下载到本地，给出md5
                // $str = md5(Storage::get('wechat/87035.jpg')); //3d1e734982e6c18a65c88dd34eac4d96
                // $str = Storage::size('wechat/87035.jpg');
            }else{ //TODO Failed Retry, 队列天然支持
                Log::error(__METHOD__, ['文件消息下载失败，请使用saveAttachmentBy(WechatMessage)重试！', $wechatMessage, $response]);
            }
        }
        $msg = WechatMessage::create($wechatMessage);
        if($needSave) Storage::put("wechat/message.".$msg->id.".rawcontent", $rawContent);
    }

    // 新增用户
    private function save_contact($who, $Wxid, $wechatBot){
        $data = [
            'userName' => $who,
            'wechat_bot_id' => $Wxid
        ];

        // 查找用户，获取其详细信息
        $wechat = new Wechat($Wxid);
        $response = $wechat->friendFind($who);
        if($response->ok() && $response['code'] === 1000){
            $data = array_merge($data, $response['data']);
        }
        
        // 保存为contact， 并更新所属bot关系
        $contact = WechatContact::create($data);
        $attach[$contact->id] =['remark'=>$contact->remark?:'', 'seat_user_id' => $wechatBot->team_id, 'type'=>3]; // 'type' => 3,// 0:friend好友,1:group群,2:public公众号,3:非好友群成员
        $wechatBot->contacts()->sync($attach);
        Log::info(__METHOD__, ['Saved new contact:', $who]);
        return $contact;
    }
}
