<?php

namespace App\Models;

use App\Services\Wechat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Plank\Metable\Metable;
use Mvdnbrk\EloquentExpirable\Expirable;

class WechatBot extends Model
{
    use Expirable; // 账户有效期 https://github.com/mvdnbrk/laravel-model-expires/
    use Metable;
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at']; // If you choose to unguard your model, you should take special care to always hand-craft the arrays passed to Eloquent's fill, create, and update methods: https://laravel.com/docs/8.x/eloquent#mass-assignment-json-columns

	use SoftDeletes;
    use LogsActivity;
    protected static $logOnlyDirty = true;
    // protected static $logAttributes = ['email', 'email_verified_at'];
    protected static $logAttributesToIgnore = ['bigHead', 'config'];
    // only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated']; //created, updated, deleted

    // Mass Assignment & JSON Columns https://laravel.com/docs/8.x/eloquent#mass-assignment-json-columns
    protected $casts = [
        'config' => 'collection' // casting the JSON database column
    ];

    
    
    // bot和contact关系 N:N
    public function contacts(): BelongsToMany // @see https://laravel.com/docs/8.x/eloquent-relationships#many-to-many
    {
        // $contact = $bot->contacts->where('userName','gh_3dfda90e39d6')->first()
        // $contact->pivot->type
        // $contact->pivot->remark
        // $contact->pivot->seat_user_id
        // $contact->pivot->config
        return $this->belongsToMany(WechatContact::class, 'wechat_bot_contact')->withPivot(['type','remark','seat_user_id','config']); //, 'wechat_bot_id', 'wechat_contact_id'
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

    //主动发送 文本消息，加入 座席seatUser 和 team
    // $content 可以是 string 、url 、Array
    
    //// 主动发送 文本
        // $text = "5/6.座席客服 主动发送 文本 消息给好友，主动记录 message + ID"
        // $response = $wechatBot->send($Wxid, $text);
    //// 主动发送 图片
        // $image = "https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg";
        // $response = $wechatBot->send($Wxid, $image);
    //// 主动发送 视频
        // $video = ['path'=>"https://abc.yilindeli.com/teach/LevelTestMaterial/0zhumuTestFiles/test.mp4", 'thumbPath'=>"https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg"];
        // $response = $wechatBot->send($Wxid, $video);
    //// 主动发送 名片
        // $card = ['nameCardId'=>"filehelper", 'nickName'=>"文件传输助手"];
        // $response = $wechatBot->send($Wxid, $card);
    //// 主动发送 链接
        // $url = ['title'=>'测试链接到百度', 'url'=>'https://weibo.com', 'description'=>'其实我放的是微博的链接', 'thumbUrl'=>"https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg"];
        // $response = $wechatBot->send($Wxid, $url);

    public function send($ToWxid, $content, $at=""):Response
    {
        $seatUser = auth()->user();
        $teamId = $this->team_id;
      
        if($seatUser){
            if($seatUser->currentTeam->id != $teamId) return "您无权限发送！"; // 判断发送者 是否属于 该bot的Team
            $seatUserId = $seatUser->id;
        }else{
            $seatUserId = $this->user_id; // 如果是 后台定时发送呢？ $seatUser == 默认bot拥有者的user_id
        }

        $Wxid = $this->userName;
        $wechat = new Wechat($Wxid);
        
        if(is_string($content)){
            if(Str::startsWith($content, 'http')){ //图片链接
                if(Str::endsWith($content, ['.jpg','.png','.jpeg','.gif'])) {
                    $msgType = WechatMessage::MSG_TYPES['image'];
                    $response = $wechat->sendImage($ToWxid, $content, $at);
                }
                // if(Str::endsWith($content, ['.mp3','mp4'])) { }
            }else{ // 普通文本
                $msgType = WechatMessage::MSG_TYPES['text'];
                $response = $wechat->sendText($ToWxid, $content, $at);
            }
        }elseif(is_array($content)){
            // $video = ['nameCardId'=>'1', 'nickName'=>2];
            if(Arr::has($content, ['path', 'thumbPath'])){
                $msgType = WechatMessage::MSG_TYPES['video'];
                $response = $wechat->sendVideo($ToWxid, $content['path'], $content['thumbPath']);
            }
            
            // $card = ['nameCardId'=>'filehelper', 'nickName'=>"文件传输助手"];
            else if(isset($content['nameCardId'])){
                $msgType = WechatMessage::MSG_TYPES['card'];
                $response = $wechat->sendCard($ToWxid, $content['nameCardId']);
            }
            
            // $link = compact('title', 'url', 'description', 'thumbUrl');
            else if(Arr::has($content, ['title', 'url', 'description', 'thumbUrl'])){
                $msgType = WechatMessage::MSG_TYPES['url'];
                
                $response = $wechat->sendUrl($ToWxid, $content);
            }
            // TODO other types
        }

        if($response->ok()){
            // 主动发送消息，需要主动记录 客服座席 user_id to message
            $wechatBot = WechatBot::firstWhere('team_id', $teamId);
            $contact = WechatContact::firstWhere('userName', $ToWxid);
            $data = [
                // 'msgId'=>NULL, // ???
                'seat_user_id' => $seatUserId, // 座席 用户ID
                // 'from_contact_id' => null, //主动发送时，应该bot的WechatContact->id, 故为NULL
                'wechat_bot_id' => $wechatBot->id,  //WechatBot
                'conversation' => $contact->id, //WechatContact
                'content' => is_array($content)?json_encode($content):$content,
                'messageType' => 2, // message.messageType int 0:好友请求 1:群邀请 2：消息 3:离线 4:其他消息
                'type' => 3, // 3:bot 主动发消息
                'msgType' => $msgType,
            ];
            $wechatMessage = WechatMessage::create($data);
            Log::info(__METHOD__, ['WechatBot::send 主动发送成功', $wechatMessage->id]);
        }
        return $response;
    }
}