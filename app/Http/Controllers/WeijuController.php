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
            Log::error(__METHOD__, ['Callback原始消息中含msgSource、pushContent', $inputs]);
            
        $messageType = $inputs['message']['messageType'];
        $wechatMessage = array_merge(compact('messageType'), $inputs['message']['data']);

        // 消息类型 type
            // 1. 好友 发消息给 bot
            // 2. 群成员 发消息给 群

            // 0 == self
                // 3.1 bot 主动发消息/被动回复 到 好友
                // 3.2 bot 主动发消息/被动响应 到 群
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
        }else{ // self：true 主动发送消息 /被动响应消息
            $wechatMessage['type'] = 0;
            $wechatMessage['wechat_bot_id'] = $wechatMessage['sendUser'];
            $wechatMessage['conversation'] = $wechatMessage['toUser'];
        }
        
        // 把 string 转换成 id 表关系 foreignId
        $Wxid = $wechatMessage['wechat_bot_id'];
        $wechatBot = WechatBot::firstWhere('userName', $Wxid);

        // - 默认 群组 消息 不存储，可以有选择的接收群消息
        // TODO in UI
            // $wechatBot->setMeta('msg_room_off', 1); // 不接收群消息
            // $wechatBot->setMeta('msg_room_only', ['1144@chatroom','x@chatroom']); // 只接收少数几个群的群消息
        $isRoom = $wechatMessage['category']; // $table->boolean('category')->default(false)->comment('int 0:私聊消息;1:群组消息');
        if($isRoom){
            if($wechatBot->getMeta('msg_room_off')){ // 不接收群消息
                Log::error(__METHOD__, ['TODO: 不接收群消息，直接返回，未开启接收', __LINE__]);
            }else{  // 接收群消息
                $roomArray = (array) $wechatBot->getMeta('msg_room_only');
                if(!in_array($wechatMessage['conversation'], $roomArray)){ // ！在白名单中
                    Log::error(__METHOD__, ['TODO: 不接收群消息，直接返回, ！在白名单中', __LINE__]);
                }
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
        if(Str::startsWith($wechatMessage['content'], ['<?xml ','<msg'])) {
            $msg = xStringToArray($wechatMessage['content']);
            if(Arr::has($msg,'appmsg.type')){ // '<?xml '
                $xmlType = $msg['appmsg']['type'];
                switch ($xmlType) {
                    case '3':
                        Log::debug(__METHOD__, ['接收到 音频：点击▶️收听']);
                        # TODO code...// appmsg.type = 3;
                        break;
                    
                    default:
                        Log::error(__METHOD__, ['接收到 未处理$xmlType消息']);
                        break;
                }
            }
            if(Arr::has($msg, 'img')){ //以<msg> <img 开头 
                $xmlType = 'image';
                $md5 = $msg['img']['@attributes']['md5'];
                $size = $msg['img']['@attributes']['length'];
                // TODO 如果已经下载了，不再下载，引用之前的链接文件！
                Log::debug(__METHOD__, ['接收到图片', $md5, $size]);
            }

            Log::debug(__METHOD__, ["Callback待处理复杂消息:$xmlType", Arr::except($wechatMessage, ['content', 'img'])]);
        }else{ // 简单消息
            Log::debug(__METHOD__, ['Callback待处理简单消息', $wechatMessage]);
        }

        
        $wechatMessage['wechat_bot_id'] = $wechatBot->id;
        $wechatMessage['conversation'] = WechatContact::firstWhere('userName', $wechatMessage['conversation'])->id;
        if(isset($wechatMessage['from_contact_id'])){
            $ToWxid = $wechatMessage['from_contact_id'];
            // 处理群成员（非好友）的信息，先保存为contact
            $contact = WechatContact::firstWhere('userName', $wechatMessage['from_contact_id']);
            if(!$contact) {
                $data = [
                    'userName' => $ToWxid,
                    'wechat_bot_id' => $Wxid,
                    'seat_user_id' => $wechatBot->team_id,
                    // 'type' => 3,// 0:friend好友,1:group群,2:public公众号,3:非好友群成员
                ];

                // 查找用户，获取其详细信息
                $wechat = new Wechat($Wxid);
                $response = $wechat->friendSearch($ToWxid);
                if($response->ok() && $response['code'] === 1000){
                    $data = array_merge($data, $response['data']);
                }
                
                // 保存为contact， 并更新所属bot关系
                $contact = WechatContact::create($data);
                $attach[$contact->id] =['remark'=>$contact->remark?:'', 'type'=>3];
                $wechatBot->contacts()->sync($attach);
            }
            $wechatMessage['from_contact_id'] = $contact->id;
        }

        // 为什么1条信息，数据库中有2个记录，同样的msgId？
            // server重发 发送2次post
            // 使用Cache 缓存要处理的条目
        // TODO 加入写入队列，使用队列写入数据库?
            
        $rawContent = $wechatMessage['content'];
        $needSave = false;
        if(in_array($wechatMessage['msgType'], WechatMessage::ATTACHMENY_MSG_TYPES)){
            $needSave = true;
            // 49 点击▶️收听， 不需要下载

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
}
