<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_messages', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedSmallInteger('msgType')->comment('int 1:文本消息 10000:表示添加别人成功;47:动图;48:地图位置; // 49:红包、文件、链接、小程序; // 49 点击▶️收听');//message.data.msgType   

            // $table->unsignedTinyInteger('old:type')->nullable()->comment('message.data.type   int 0:文本消息（49也是0）;1:图片消息；2：视频消息');
            $table->unsignedTinyInteger('type')->nullable()->comment('1:好友 发消息给 bot 2:群成员 发消息给 群 3:bot 主动发消息');
            // $table->boolean('self')->default(false)->comment('boolean:true为bot主动发送消息'); // message.data.self   boolean:false   主动发送消息？
            // $table->boolean('category')->default(false)->comment('int 0:私聊消息;1:群组消息');

            // $table->string('toUser', 32)->comment('String  接收微信号 == bot'); //message.Wxid = message.data.toUser   
            $table->foreignId('wechat_bot_id')->nullable()->comment('接收微信号botId');

            // $table->string('sendUser', 32)->comment('String 消息发送者');//message.sendUser 发送者
            $table->foreignId('conversation')->nullable()->comment('会话对象');

            // 从手机微信主动发信息时： from_contact_id 和 seat_user_id 都为NULL
            // 网页版主动发送信息时，seat_user_id为座席用户id， msgId 为null

            // $table->string('fromUser', 32)->nullable()->index()->comment('String 消息发送到的群/发送者');//message.fromUser “xx@chatroom”
            $table->foreignId('from_contact_id')->nullable()->comment('消息接收者，= toUser, 主动时发送除外，应该补充为botId');
            $table->foreignId('seat_user_id')->nullable()->comment('主动回复时的客服ID');
            $table->unsignedbigInteger('msgId')->nullable()->comment('message.data.msgId long    消息ID: 1116020096');
            $table->text('content')->nullable()->comment('String(文本消息) 或 XML（图片、视频消息）消息体'); // message.data.content    String  
            // $table->text('img')->default('')->comment(''); // message.data.img byte[]  缩略图
            
            // $table->text('atlist')->default('')->comment(''); // message.data.atlist   String[]    被艾特群成员微信号
            // "atlist":["<silence>1</silence>\n\t<membercount>7</membercount>"]
            // "atlist":["wxid_7n d22","kexke302"]
            
            // $table->text('msgSource')->default('')->comment('');// ！message.data.msgSource    String  消息源
            // $table->text('pushContent')->default('')->comment('String:谁@了bot你');// message.data.pushContent String  
            // $table->unsignedInteger('timestamp')->comment('消息原始时间戳long：1613208757');// message.data.timestamp
            $table->unsignedsmallInteger('messageType')->comment('message.messageType int 0:好友请求 1:群邀请 2：消息 3:离线 4:其他消息');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    // message.data.msgType 
        // int  10000:表示添加别人成功;47:动图;48:地图位置;
        // 49:红包、文件、链接、小程序; // 49 点击▶️收听
            //（具体根据content中的type字段区分，
            // 当type为2001时表示红包，
            // 2000表示收付款消息，
            // 5表示链接，
            // 19表示群聊的聊天记录，
            // 33和36表示小程序，6表示文件，
            // 8表示手机输入法自带表情），
            // 100008表示手机上删除好友，from_user表示好友的wxid
     
        //--------------------------------------------------------------------------------------------
        // tinyint     | 1 byte   -128 to 127                                  0 to 255
        // smallint    | 2 bytes  -32768 to 32767                              0 to 65535
        // mediumint   | 3 bytes  -8388608 to 8388607                          0 to 16777215
        // int/integer | 4 bytes  -2147483648 to 2147483647                    0 to 4294967295
        // bigint      | 8 bytes  -9223372036854775808 to 9223372036854775807  0 to 18446744073709551615 

        // 什么情况下不推荐使用索引？ https://blog.csdn.net/kaka1121/article/details/53395628
        //     1) 数据唯一性差（一个字段的取值只有几种时）的字段不要使用索引
        //         比如性别，只有两种可能数据。意味着索引的二叉树级别少，多是平级。这样的二叉树查找无异于全表扫描。
        //     2) 频繁更新的字段不要使用索引
        //     3) 字段不在where语句出现时不要添加索引,如果where后含IS NULL /IS NOT NULL/ like ‘%输入符%’等条件，不建议使用索引
        //         只有在where语句出现，mysql才会去使用索引
        //     4） where 子句里对索引列使用不等于（<>），使用索引效果一般

        // 群消息查询 WHERE category=room & roomName = fromUser （windex）
        // 个人聊天消息查询 WHERE category=personal & who = sendUser （数据唯一性差（一个字段的取值只有几种时）的字段不要使用索引）
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_messages');
    }
}
