<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_bots', function (Blueprint $table) {
            $table->id();

            // Bot 和 team 关系 1:1 
            $table->foreignId('team_id')->nullable()->comment('初始化管理员所在的personal_team'); // '初始化管理员' = $team->owner->id

            $table->string('userName', 32)->index()->unique();
            $table->string('nickName')->default('');
            $table->string('bindEmail')->default('');
            $table->string('bindMobile')->default('');
            $table->boolean('sex')->default(false)->comment('0:女1:男');
            $table->string('signature')->default('');
            $table->string('bigHead')->default('');
            
            $table->expires()->comment('有效期');
            // $subscription->expires_at = now()->addMinutes(10);
            // if ($subscription->expired()) {
            //     //
            // }
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
        Schema::dropIfExists('wechat_bots');
    }
}
