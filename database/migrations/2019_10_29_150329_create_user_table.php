<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('用户id');
            $table->string('openid', 64)->comment('用户openid');
            $table->string('nickname', 32)->comment('昵称');
            $table->string('phone', 16)->comment('手机');
            $table->smallInteger('sex', 2)->comment('1男');
            $table->string('country', 32)->comment('国家');
            $table->string('province', 32)->comment('省');
            $table->string('city', 32)->comment('市');
            $table->string('language', 16)->comment('语言');
            $table->string('headimgurl', 64)->comment('图片');
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
        Schema::dropIfExists('user');
    }
}
