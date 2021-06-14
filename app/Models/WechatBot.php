<?php

namespace App\Models;

use App\Jobs\WechatAgreeQueue;
use App\Services\Wechat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Plank\Metable\Metable;
use Mvdnbrk\EloquentExpirable\Expirable;

class WechatBot extends Model
{
    use Expirable;
    use Metable;
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at']; // If you choose to unguard your model, you should take special care to always hand-craft the arrays passed to Eloquent's fill, create, and update methods: https://laravel.com/docs/8.x/eloquent#mass-assignment-json-columns

	use SoftDeletes;
    use LogsActivity;
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = ['bigHead', 'config'];
    // only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated']; //created, updated, deleted

    // Mass Assignment & JSON Columns https://laravel.com/docs/8.x/eloquent#mass-assignment-json-columns
    protected $casts = [
        'config' => 'collection' // casting the JSON database column
    ];

    protected $dates = ['created_at', 'updated_at', 'expires_at', 'login_at'];
    
    public function scopeActive($query)
    {
        return $query->whereNotNull('login_at');
    }

    // bot和contact关系 N:N
    protected $touches = ['contacts']; //https://github.com/laravel/framework/issues/31597
    public function contacts(): BelongsToMany // @see https://laravel.com/docs/8.x/eloquent-relationships#many-to-many
    {
        // $contact = $bot->contacts->where('userName','gh_3dfda90e39d6')->first()
        // $contact->pivot->type
        // $contact->pivot->remark
        // $contact->pivot->seat_user_id
        // $contact->pivot->config
        return $this->belongsToMany(WechatContact::class, 'wechat_bot_contacts')
            ->withTimestamps()
            ->withPivot(['type','remark','seat_user_id']); //, 'wechat_bot_id', 'wechat_contact_id'
    }

    // WechatBot::find(1)->autoReplies()->create(['keyword'=>'hi','wechat_content_id'=>1]);
    public function autoReplies()
    {
        return $this->hasMany(WechatAutoReply::class)
            ->orderBy('updated_at','desc'); // 最近编辑的，作为第一个匹配来响应
    }

    // 1:1
    public function team(){
        return $this->belongsTo(Team::class);
    }

    // get by $this->wxid;
    public function getWxidAttribute()
    {
        // $Wxid = $wechatBot->userName;
        return $this->userName;
    }

    // $wechatBot->wechat;
    public function getWechatAttribute():Wechat { return new Wechat($this->userName);}


    public function addBySearch($Wxid)
    {
        $response = $this->wechat->friendFind($Wxid);
        if($response->ok() && Arr::has($response,['data.v1','data.v2'])){
            // /foreign/friends/add
            $v1 = $response['data']['v1'];
            $v2 = $response['data']['v2'];
            dd($this->wechat->friendAdd($v1, $v2)->json());
        }
        return $response;
    }

    // 可以主动发送的消息类型
    // const MSG_TYPES = [文本,图片,视频,名片,链接,小程序]
    //主动发送 文本消息，加入 座席seatUser 和 team
    
    // $tos = ['802'=>'filehelper',...] //Array or Collection id可以不要 
    // content 字段名字给API有关，不可以随意更改！
    // $text = ['type'=>'text','content'=>"send text"];
    // $text = ['type'=>'image','content'=>"https://your.com/test.jpg"];
    // $url = ['type'=>'url','title'=>'linkTitle', 'url'=>'https://weibo.com', 'description'=>'this is a link', 'thumbUrl'=>"https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg"];
    // $card = ['type'=>'card','nameCardId'=>'wxid_xxx', 'nickName'=>'nothing']];
    // $video = ['type'=>'video','path'=>"https://your.com/test.mp4", 'thumbPath'=>"https://your.com/test.jpg"];
    // 批量发送
    public function sendTo($tos, WechatContent $wchatContent){
        foreach ($tos as $to) {
            $this->send($to, $wchatContent);
        }
    }

    /**
     * 带返回结果，单个发送
     */
    public function send(string $wxid, WechatContent $wchatContent)
    {
        $typeId = $wchatContent->type;
        $content = $wchatContent->content;

        $seatUser = auth()->user();
        $teamId = $this->team_id;
      
        if($seatUser){
            if($seatUser->currentTeam->id != $teamId) return "您无权限发送！"; // 判断发送者 是否属于 该bot的Team
            $seatUserId = $seatUser->id;
        }else{
            $seatUserId = $this->team->user_id; // 如果是 后台定时发送呢？ $seatUser == 默认bot拥有者的user_id
        }
        
        $Wxid = $this->userName;
        
        $typeName = WechatContent::TYPES[$typeId];
        $sendType = Str::camel("send_{$typeName}");//sendImage  sendText sendVideo sendCard sendUrl .strtoupper
        
        // foreach ($tos as $wxid) {
            if($typeName == 'template'){
                // 可用变量替换
                $template = $wchatContent->content['content'];
                
                $contact = \App\Models\WechatBotContact::with('contact','seat')
                    ->whereHas('contact', fn($q)=>$q->where('userName', $wxid))
                    ->firstOrFail();
                // :remark 备注或昵称 
                // :name 好友自己设置的昵称 
                // :seat 客服座席名字 
                // :no 第x号好友
                $remark = $contact->remark;
                $name = $contact->contact->nickName;
                $seat = $contact->seat->name;
                $no = $contact->id;

                $replaced = preg_replace_array('/:remark/', [$remark], $template);
                $replaced = preg_replace_array('/:name/', [$name], $replaced);
                $replaced = preg_replace_array('/:seat/', [$seat], $replaced);
                $replaced = preg_replace_array('/:no/', [$no], $replaced);
                
                
                //  Indirect modification of overloaded property
                // $data = $wchatContent->content;
                // $data['data']['content'] = $replaced;
                // $wchatContent->content = $data;
                // $content = $replaced;
                $content = ['content'=>$replaced];//$wchatContent->content;
                $sendType = 'sendText';
            }
            // "card",//名片 5
            // "url",//链接 6
            if(in_array($typeId,[5,6])){
                $cnType = WechatContent::TYPES_CN[$typeId];
                $content['content'] = "已发送{$cnType}消息,请到手机上查看";
            }
            $contentWithTo = array_merge(['ToWxid'=> $wxid], $content);
            
            $response = $this->wechat->send($sendType, $contentWithTo);
            if($response->ok() && $response['code'] == 1000){ // 1000成功，10001失败
                Log::info(__METHOD__, ['主动发送成功', $this->nickName, $wxid]);
                // 主动发送消息，需要主动记录 客服座席 user_id to message
                // $wechatBot = WechatBot::firstWhere('team_id', $teamId);
                $contact = WechatContact::where('userName', $wxid)->first(); //初始化还未完成，就发送消息了！
                if(!$contact) return; // Error! 发送给一个未知的用户/群
                $data = [
                    // 'msgId'=>NULL,
                    'seat_user_id' => $seatUserId, // 座席 用户ID
                    // 'from_contact_id' => null, //主动发送时，应该bot的WechatContact->id, 故为NULL
                    'wechat_bot_id' => $this->id,  //WechatBot
                    'conversation' => $contact->id, //WechatContact
                    'content' => $content,
                    // 'type' => 3, // 3:bot 主动发消息
                    'msgType' => WechatMessage::MSG_TYPES[$typeName],
                ];
                return WechatMessage::create($data);
            }else{
                Log::error(__METHOD__, ['主动发送失败', $Wxid, $wxid, $response->json(), $sendType, $contentWithTo]);
                return false;
            }
        // }
    }

    // $wechatBot = WechatBot::find(1);
    // $wechat =  new Wechat($wechatBot->userName);
    // $ToWxid = 'bluesky_still';
    // $response = $wechat->friendFind($ToWxid);
    // $response = $wechat->friendAdd($response['data']['v1'],$response['data']['v2']);
    public function addFriend($ToWxid, $message="", $tryTimes = 3)
    {
        $Wxid = $this->userName;
        $wechat = $this->wechat;
        $message = $message?: '我是' . $this->nickName;

        // 搜索用户，获取v1、v2数据， rescue:网络出错是不报错、不终止，而是等待x秒重试3次
        $count = 0;
        do{
            sleep(pow($count,2)); //0 1s 4s
            $response1 = rescue(fn()=>$wechat->friendFind($ToWxid), null, false);
        }while(!$response1 && $count++<$tryTimes);

        // 执行添加好友操作
        if($response1->ok() && Arr::has($response1,['data.v1','data.v2'])){
            $v1 = $response1['data']['v1'];
            $v2 = $response1['data']['v2'];
            $count = 0;
            do{
                sleep(pow($count,2)); //0 1s 4s
                $response2 = rescue(fn()=>$wechat->friendAdd($v1, $v2, $message), null, false);
            }while(!$response2 && $count++<$tryTimes);
            if($response2->ok() && $response2['code'] == 1000){ // 1000成功，10001失败
                return ['success' => true];
                Log::info(__METHOD__, ['主动添加好友成功', $Wxid, $ToWxid]);
            }else{
                Log::error(__METHOD__, ['主动添加好友失败2', $Wxid, $ToWxid, $response1->json(), $response2->json()]);
            }
        }else{
            Log::error(__METHOD__, ['加好友失败，对方可能已是好友', $Wxid, $ToWxid, $response1->json()]);
        }
        return ['success' => false];
    }

    // 同意添加好友请求
    public function friendAgree($v1, $v2, $wxid)
    {
        $delaySeconds = rand(60, 120);
        WechatAgreeQueue::dispatch($v1, $v2, $this, $wxid)->delay(now()->addSeconds($delaySeconds));
    }

    public function friendDel($wxid){ return $this->wechat->friendDel($wxid); }


    public function wechat()
    {
        return new Wechat($this->userName);
    }

    // 新增用户
        // 'public'=>0, // 0
        // 'friend'=>1, // 1
        // 'group'=>2, // 2
        // 'stranger'=>3, // 3
    public function addOrUpdateContact($wxid, $type=1){
        $data = [
            'userName' => $wxid,
            'wechat_bot_id' => $this->id,
        ];

        // 查找用户，获取其详细信息
        $response = $this->wechat->friendFind($wxid);
        if($response->ok() && $response['code'] === 1000){
            $data = array_merge($data, $response['data']);
            // 保存为contact， 并更新所属bot关系
            ($contact = WechatContact::firstWhere('userName', $data['userName']))
            ? $contact->update($data) // 更新资料
            : $contact = WechatContact::create($data);

            $attach[$contact->id] =[
                'remark' => $contact->remark?:$contact->nickName, 
                'seat_user_id' => $this->team->user_id,
                'type' => $type
            ];
            $this->contacts()->syncWithoutDetaching($attach);
            return $contact;
        }else{
            Log::error(__METHOD__, [$data, $response->json()]);
        }
    }

    public function setCallBackUrl($callbackSend=null, $heartBeat='', $linkMsgSend='')
    {
        $response = $this->wechat->setCallBackUrl($callbackSend, $heartBeat, $linkMsgSend);
        Log::info(__METHOD__, [compact('callbackSend','heartBeat','linkMsgSend'), $response->json()]);
    }

    // call in queue
    public function syncContacts(){
        Log::info(__METHOD__,['sync begin',$this->userName]);
        $wechat = $this->wechat;
        // InitWechat::dispatch($Wxid); // 500联系人init()需要2分钟 
        $wechat->init(); //为什么要init，不init可以用吗？
        Log::info(__METHOD__,['init done',$this->userName]);

        // 初始化标签
        // 1.记录 手机微信里的wxLabels ID和对应名称labelName
        $wxLabels = [];
        $response = $wechat->getLables();
        if($response->ok() && $response['code'] == 1000){
            foreach ($response['data'] as $label) {
                $wxLabels[$label['labelID']] = $label['labelName'];
            }
        }else{
            Log::error(__METHOD__, [__LINE__, '初始化标签', '失败', $response]);
        }

        # 保存 bot 的通讯录信息（不含群成员）
        $response = $wechat->getAllContacts();
        if($response->ok() && $response['code'] == 1000){
            $attachs = [];
            $tags = [];
            $teamOwnerId = $this->team->owner->id;
            foreach ($response['data'] as $type => $values) {
                foreach ($values as $data) {
                    ($contact = WechatContact::firstWhere('userName', $data['userName']))
                        ? $contact->update($data) // 更新资料
                        : $contact = WechatContact::create($data);

                    // 2.记录 每个联系人的标签
                    if($type == 'friend' && isset($data['labelIdList'])){

                        $wxLabelIds = explode(',', $data['labelIdList']);
                        foreach ($wxLabelIds as $wxLabelId) {
                            $tags[$contact->id][] = $wxLabels[$wxLabelId];
                        }
                    }

                    $wechatBotContact = WechatBotContact::where('wechat_bot_id', $this->id)
                        ->where('wechat_contact_id', $contact->id)->first();
                    if($wechatBotContact && $wechatBotContact->remark!==null) continue; //已经存在的不用更新，防止备注被覆盖！

                    $remark = $data['remark']??null;
                    $nickName = $data['nickName']??null;
                    $remark = $remark??$nickName;
                    $attachs[$contact->id] = [
                        'remark' => $remark,
                        'type' => WechatContact::TYPES[$type],
                            // 'public'=>0, // 0
                            // 'friend'=>1, // 1
                            // 'group'=>2, // 2
                            // 'stranger'=>3, // 3
                        'seat_user_id' => $teamOwnerId,
                    ];// @see https://laravel.com/docs/8.x/eloquent-relationships#updating-many-to-many-relationships
                    
                }
                Log::info(__METHOD__, ["SyncWechat", "通讯录", $type, count($values)]);
            }
            $this->syncTags($tags);
            $this->contacts()->syncWithoutDetaching($attachs);
            Log::debug(__METHOD__,['已同步', $this->wxid, $attachs]);
        }else{
            Log::error(__METHOD__, [__LINE__, $response]);
        }
    }

    public function syncTags($tags)
    {
        // 3.写入 每个联系人的标签
        // $tags = [];
        $tagWith = 'wechat-contact-team-' . $this->team->id;
        foreach ($tags as $contactId => $tagNames) {
            $wechatBotContact = WechatBotContact::where('wechat_bot_id', $this->id)
                ->where('wechat_contact_id', $contactId)
                ->first();
            foreach ($tagNames as $tagName) {
                $wechatBotContact->attachTag($tagName, $tagWith);
            }
        }
    }
}