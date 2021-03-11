<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

            // // 注意2个type
            // $contents[] = ['type'=>'schedule','data'=>[
            //     'send_by' => array_rand(['count', 'date', null]),
            //     'type' => 'file',
            //     ''

            //     ]
            // ];
            
            // // "taskId": 1,
            // // "cron": "0 7 * * *", // or null：一次性发送
            // // "send_at": "2021-02-27 18:00:00" or null：// 批量发送记录
            // // "offset": "2020-2-25" // (send_by: count时)
            // // to: wechat_contacts.id （大多数都是群）

        Schema::create('wechat_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wechat_task_id');
            // 发送给谁
                // 1.分组发送 按照标签分组 byTagWith(); 
                $table->foreignId('tag_id')->nullable();
                // 2.手动选择，发送给某几个人！
                    // 搜索好友，选择好友，加入带发送数组 sendTo[]=[ToWxid=>'userName'] 
                    // $selected['userName'] = $wechatContact->nickName;
                    // json_encode($selected)
                    $table->text('send_to')->nullable(); //@see https://stackoverflow.com/questions/13932750/tinytext-text-mediumtext-and-longtext-maximum-storage-sizes
                // 3.发送给所有好友：ALL (这种类型的任务，不适合每个人都发！)
            $table->timestamp('offset_at')->nullable()->comment('从哪一天开始发送第0、1个');
            $table->unsignedInteger('count')->nullable()->comment('当前发送到第N个，0为第一个');
            // 每天几点发送？ 每隔几天发送？
            $table->string('cron')->nullable()->comment("发送规律：* * * * *");
            

            // 可 取消订阅/取消发送
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_message_subscriptions');
    }
}
