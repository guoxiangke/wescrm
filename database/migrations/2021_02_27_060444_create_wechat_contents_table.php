<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_contents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('简短话术描述、群发备注');
            // TODO 以防止切换team后发送
            $table->foreignId('wechat_bot_id');
            //TODO 可用变量 替换 
                // 昵称、:nickName
                // 备注、{$mark} 
                // 微信Id、 {$wxid}
                // 先生/女士/未知 {$sex}
                // 专属客服名字 {$seatUserName}
            // @see https://laravel.com/docs/8.x/helpers#method-preg-replace-array
            // $string = 'The event will take place between :start and :end';
            // $replaced = preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], $string);
            
            $table->unsignedSmallInteger('type')->default(1)->comment('发送类型：0:含有模版变量的文本，1:纯文本, ...');
            $table->json('content');

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
        Schema::dropIfExists('wechat_contents');
    }
}
