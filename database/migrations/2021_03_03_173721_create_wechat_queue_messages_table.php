<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO 批量发送任务和记录（1次性群发）
// https://pusher.com/tutorials/monitoring-laravel-background-queues
class CreateWechatQueueMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_queue_messages', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->integer("wechat_bot_id")->unsigned()->index()->comment('所属bot');// 不过内容里有 wechat_bot_id 了。
            $table->integer("wechat_contact_id")->unsigned()->index()->comment('发送给谁');
            $table->integer("wechat_content_id")->unsigned()->index()->comment('回复的内容');
            $table->timestamp('run_at')->nullable()->comment('job发送时间');
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
        Schema::dropIfExists('wechat_queue_messages');
    }
}
