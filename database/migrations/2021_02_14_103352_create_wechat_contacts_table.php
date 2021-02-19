<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_contacts', function (Blueprint $table) {
            $table->id();
            
            $table->string('userName')->index()->unique()->comment('friend: Wxid, public: gh_xxx, group: 123@chatroom');
            $table->string('nickName')->default('')->comment('friend都有');
            $table->string('aliasName')->default('')->comment('public才有');
            // $table->string('labelIdList')->default('')->comment('friend标签id');
            // $table->string('remark')->default('')->comment('friend有，Init-remark');
            $table->unsignedTinyInteger('sex')->default(0)->comment('0未知，1男，2女');
            $table->string('country')->default('')->comment('friend、public有');
            $table->string('province')->default('')->comment('friend、public有');
            $table->string('city')->default('')->comment('friend、public有');

            $table->string('signature')->default('')->comment('friend、public有');
            $table->string('bigHead')->default('')->comment('friend都有');
            $table->string('smallHead')->default('')->comment('friend都有');
            
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
        Schema::dropIfExists('wechat_contacts');
    }
}
