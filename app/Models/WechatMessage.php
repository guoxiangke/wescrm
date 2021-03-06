<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// 从手机微信主动发信息时： from_contact_id 和 seat_user_id 都为NULL
// 网页版主动发送信息时，seat_user_id为座席用户id， msgId 为null
// - 不删除数据，设置 expired_at 为90天后！默认查询scope为  expired_at！=null
class WechatMessage extends Model
{
    use HasFactory;
    // 'updated_at', 更新时间，设置为 返回消息中的时间
    // TODO scope 默认 orderBy  desc
    protected $guarded = ['id', 'created_at', 'deleted_at', 'updated_at'];

	use SoftDeletes;
    
    protected $casts = [
        'content' => 'array',
    ];
    
    // WechatMessage 消息逻辑类型
        // 1. 好友 发消息给 微信bot
        // 2. 群成员 发消息给 微信bot 的群
        // 3 3.1 bot 通过手机发送消息给 好友
        // 4 3.2 bot 通过手机发送消息给 群
        // 5. 主动发送消息给 好友，主动记录 message + 座席ID 
        // 6. 主动发送消息到 群里，主动记录 message + 座席ID
    // 数据库设计
        // type：'1:好友 发消息给 bot 2:群成员 发消息给 群 3:bot 主动发消息'
        // self：'boolean:true为bot主动发送消息'
        // category：'int 0:私聊消息;1:群组消息'
        // wechat_bot_id：'接收微信号botId'
        // conversation：'会话对象');
        // from_contact_id：消息接收者，= toUser, 主动时发送除外，应该补充为botId');
        // msgId：message.data.msgId long    消息ID: 1116020096');
        // content：'String(文本消息) 或 XML（图片、视频消息）消息体');
        // seat_user_id：'主动回复时的客服ID');

    // MESSAGE_TYPES int	0:好友请求 1:群邀请 2：消息 3:离线 4:其他消息
    
    // Attachment
    const ATTACHMENY_MSG_TYPES = [
        3, // Img
        6, // File文件
        34, // Voice
        43, // Video
        496, // 49+6
    ];

    const APP_MSG_TYPES = [
        3, //收到 音频：点击▶️收听
        33, //收到 小程序

    ];

    // 纯文本 类型消息
    const MSG_TYPES_SIMPLE = [
        1, //文本
        10000, //你已添加了xxx，现在可以开始聊天了。
    ];

    // array_flip(WechatMessage::MSG_TYPES)
    const MSG_TYPES = [
        'text'=>1, //文本
        // "msgType":1,"messageType":2,"content":"我通过了你的朋友验证请求，现在我们可以开始聊天了"
        'template'=>1, //发送带模版的文本 @see WechatBot::send(167)
        'image'=>3, // Img
        'url'=>5,// 5表示链接，
        'file'=>6,// 6表示文件
        'roomChatRecords'=>19,// 19表示群聊的聊天记录，
        'miniapp'=>331, //自定义 331 miniapp
        'voice'=>34, // Voice
        'voiceLink'=>301, //自定义 49文件 点击▶️收听 
        'requestFriend' => 37,
        // 收到好友请求
            // "msgType":37 "messageType":2  <msg encryptusername="v3_xx@stranger" ticket="v4_xxx@stranger"  fromnickname=\"天空蔚蓝\" content=\"你好\"
            // 通过朋友推荐的名片，添加Bot为好友
        // 回复好友请求，没有消息
        'video'=>43, // Video 
        'emoji'=>47, // emoji
        'geo'=>48, // 地理位置
        'file'=>496, // 文件 // 49 点击▶️收听
        'refermsg'=>491, //引用  57
        'addByIm' =>65, //  checked 
            // 我是xxx的xxx，添加我的企业微信与我联系吧。
        'hongbao'=>2001,//自定义 2001 红包
        'agreeAddByIm' => 10000, //25984xxx7841966@openim
            // "msgType":10000, ,"content":"你被\"天空蔚蓝\"移出群聊"
            // 对方为企业微信用户，<_wc_custom_link_ color="#2782D7" href="https://weixin.qq.com/cgi-bin/readtemplate?t=work_wechat/about">了解更多</_wc_custom_link_>。
            // "msgType":10000, "content":"你已添加了天空蔚蓝，现在可以开始聊天了。"
            // "msgType":10000, "content":"天空蔚蓝开启了朋友验证，你还不是他（她）朋友。请先发送朋友验证请求，对方验证通过后，才能聊天。<a href=\"weixin://findfriend/verifycontact\">发送朋友验证</a>
        'inviteToRoom'=>10002,
        // 10002
            // 邀请好友入群  // "$username$\"邀请你加入了群聊，群聊参与人还有：$others$"
            // "$username$\"修改群名为“$remark$”
        'card' => 0,//?
    ];

    // message.data.msgType	
        // int	10000:表示添加别人成功;47:动图 emoji;48:地图位置;
        // 49:红包、文件、链接、小程序; // 49 点击▶️收听
            //（具体根据content中的type字段区分，
            // 当type为2001时表示红包，
            // 2000表示收付款消息，
            // 33和36表示小程序，6表示文件，
            // 8表示手机输入法自带表情），
            // 100008表示手机上删除好友，from_user表示好友的wxid

    const MESSAGE_CATEGORY = [
        '个人消息', //0
        '群组消息', //1
    ];

    // 1:1
    public function contact(){
        return $this->hasOne(WechatContact::class, 'id', 'conversation');
    }


    public function from(){
        return $this->hasOne(WechatContact::class, 'id', 'from_contact_id');
    }


    public function wechatBot(){
        return $this->hasOne(WechatBot::class, 'id', 'wechat_bot_id');
    }

    public function seat(){
        return $this->hasOne(User::class, 'id', 'seat_user_id');
    }
}
