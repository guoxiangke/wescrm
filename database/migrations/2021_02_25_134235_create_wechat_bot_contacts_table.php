<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatBotContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_bot_contacts', function (Blueprint $table) {
            $table->id();
            // bot和contact关系 N:N
            // @see https://laravel.com/docs/8.x/eloquent-relationships#many-to-many

            // https://www.codecheef.org/article/laravel-tips-to-set-foreign-key-in-laravel-migration
            $table->foreignId('wechat_bot_id')->index();
            $table->foreignId('wechat_contact_id')->index();

            $table->unsignedTinyInteger('type')->default(0)->comment('0:friend好友,1:group群,2:public公众号,3:非好友群成员');
            $table->string('remark')->nullable()->comment('friend有，New-Remark');
            $table->foreignId('seat_user_id')->comment('转接/分配/负责的客服，默认为bot拥有者/管理者');
            $table->json('config')->nullable()->comment('配置JSON：单独的群配置，好友配置，公号配置，都在这里');
            
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
        Schema::dropIfExists('wechat_bot_contacts');
    }
}
