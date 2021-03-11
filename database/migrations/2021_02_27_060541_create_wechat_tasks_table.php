<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('计划任务描述');
            $table->foreignId('wechat_bot_id');
            // null:series => need get Type from config!
            $table->unsignedSmallInteger('type')->nullable()->comment('发送类型： null:series 0:含有模版变量的文本，1:纯文本, ...');
            // [send_by=>count, config=>["count": 150, "pad": 3, "fill": "0",'path'=>]] // 001.mp3~150.mp3
            // [send_by=>date, config=>['format'=>'ymd','path'=>]] //20210227 
            // [send_by=>series, config=>[wechatContentId1,id2,id3,id4...]
            // "path": "https://xxx.xxx.cn/PrayEveryday/videos/${current}.text",
            $table->unsignedSmallInteger('send_by')->default(0)->comment('0:count，1:date');
            $table->json('config');
            
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
        Schema::dropIfExists('wechat_tasks');
    }
}
