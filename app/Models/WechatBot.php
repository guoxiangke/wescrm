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
    // data里的字段名字给API有关，不可以随意更改！
    // $text = ['type'=>'text','data'=>['content'=>"send text"]];
    // $text = ['type'=>'image','data'=>['content'=>"https://your.com/test.jpg"]];
    // $url = ['type'=>'url','data'=>['title'=>'linkTitle', 'url'=>'https://weibo.com', 'description'=>'this is a link', 'thumbUrl'=>"https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg"]];
    // $card = ['type'=>'card','data'=>['nameCardId'=>'wxid_xxx', 'nickName'=>'nothing']];
    // $video = ['type'=>'video','data'=>['path'=>"https://your.com/test.mp4", 'thumbPath'=>"https://your.com/test.jpg"]];
    public function send($tos, WechatContent $wchatContent)
    {
        $typeId = $wchatContent->type;
        $content = $wchatContent->content['data'];

        $seatUser = auth()->user();
        $teamId = $this->team_id;
      
        if($seatUser){
            if($seatUser->currentTeam->id != $teamId) return "您无权限发送！"; // 判断发送者 是否属于 该bot的Team
            $seatUserId = $seatUser->id;
        }else{
            $seatUserId = $this->team->user_id; // 如果是 后台定时发送呢？ $seatUser == 默认bot拥有者的user_id
        }
        
        $Wxid = $this->userName;
        $wechat = new Wechat($Wxid);
        
        $typeName = WechatContent::TYPES[$typeId];
        $sendType = Str::camel("send_{$typeName}");//sendImage  sendText sendVideo sendCard sendUrl .strtoupper
        
        foreach ($tos as $wxid) {
            if($typeName == 'template'){
                // 可用变量替换
                $template = $wchatContent->content['data']['content'];
                
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
                $content = ['content'=>$replaced];//$wchatContent->content['data'];
                $sendType = 'sendText';
            }
            $contentWithTo = array_merge(['ToWxid'=> $wxid], $content);
            
            $response = $wechat->send($sendType, $contentWithTo);
            if($response->ok() && $response['code'] == 1000){ // 1000成功，10001失败
                // 主动发送消息，需要主动记录 客服座席 user_id to message
                $wechatBot = WechatBot::firstWhere('team_id', $teamId);
                $contact = WechatContact::where('userName', $wxid)->firstOrFail(); //初始化还未完成，就发送消息了！
                $data = [
                    // 'msgId'=>NULL,
                    'seat_user_id' => $seatUserId, // 座席 用户ID
                    // 'from_contact_id' => null, //主动发送时，应该bot的WechatContact->id, 故为NULL
                    'wechat_bot_id' => $wechatBot->id,  //WechatBot
                    'conversation' => $contact->id, //WechatContact
                    'content' => $content,
                    'type' => 3, // 3:bot 主动发消息
                    'msgType' => WechatMessage::MSG_TYPES[$typeName],
                ];
                return $wechatMessage = WechatMessage::create($data);
                Log::info(__METHOD__, ['WechatBot::send 主动发送成功', $Wxid, $contact->nickName, $wechatMessage->id]);
            }else{
                Log::error(__METHOD__, [__LINE__, $response->json(), $sendType, $contentWithTo]);
            }
        }
    }

    // $wechatBot = WechatBot::find(1);
    // $wechat =  new Wechat($wechatBot->userName);
    // $ToWxid = 'bluesky_still';
    // $response = $wechat->friendFind($ToWxid);
    // $response = $wechat->friendAdd($response['data']['v1'],$response['data']['v2']);
    public function addFriend($ToWxid, $message="", $tryTimes = 3)
    {
        $Wxid = $this->userName;
        $wechat = new Wechat($Wxid);
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
                Log::info(__METHOD__, ['主动添加好友成功', $Wxid, $ToWxid]);
            }else{
                Log::error(__METHOD__, ['主动添加好友失败2', $Wxid, $ToWxid, $response1->json(), $response2->json()]);
            }
        }else{
            Log::error(__METHOD__, ['加好友失败，对方可能已是好友', $Wxid, $ToWxid, $response1->json()]);
        }
    }

    // 同意添加好友请求
    public function friendAgree($v1, $v2, $wxid, $delaySeconds=5)
    {
        WechatAgreeQueue::dispatch($v1, $v2, $this, $wxid)->delay(now()->addSeconds($delaySeconds));
    }


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
                'seat_user_id' => $this->team_id, 
                'type' => $type
            ];
            $this->contacts()->syncWithoutDetaching($attach);
            Log::info(__METHOD__, [$wxid, $contact]);
            return $contact;
        }else{
            Log::error(__METHOD__, [$response->json()]);
        }
    }

    public function setCallBackUrl($callbackSend=null, $heartBeat='', $linkMsgSend='')
    {
        $response = $this->wechat->setCallBackUrl($callbackSend, $heartBeat, $linkMsgSend);
        Log::info(__METHOD__, [compact('callbackSend','heartBeat','linkMsgSend'), $response->json()]);
    }
}