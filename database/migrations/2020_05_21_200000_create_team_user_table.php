<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id');
            $table->foreignId('user_id');
            $table->string('role')->nullable();
            $table->timestamps();

            // Feature: 添加成员/客服座席 到 苹果客服‘s Team （30/人/月 300/人/年）
            // TODO 1.team成员列表页面：显示 过期时间 2.付费后，增加到30天！3.如果过期，自动退出，提示付费
                // @see App\Actions\Jetstream\InviteTeamMember
                // https://github.com/mvdnbrk/laravel-model-expires
                    // if ($subscription->expired())
            $table->expires()->default(now()->addDays(3))->comment('默认添加座席3天有效');
            $table->unique(['team_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_user');
    }
}
