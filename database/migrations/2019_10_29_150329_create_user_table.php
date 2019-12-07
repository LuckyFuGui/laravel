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
            $table->Increments('id')->comment('用户id');
            $table->string('openid', 64)->comment('用户openid');
            $table->string('nickname', 32)->comment('昵称');
            $table->string('phone', 16)->comment('手机');
            $table->integer('sex')->unsigned()->default(1)->comment('1男');
            $table->string('country', 32)->comment('国家');
            $table->string('province', 32)->comment('省');
            $table->string('city', 32)->comment('市');
            $table->integer('status')->default(1)->comment('用户状态 1：正常；0：禁用');
            $table->integer('is_recommend')->default(0)->comment('是否推荐 0：否；1：是');
            $table->integer('integral')->default(0)->comment('积分');
            $table->integer('volume')->default(0)->comment('首次优惠卷：0没领取，1领取');
            $table->string('language', 16)->comment('语言');
            $table->string('headimgurl', 256)->comment('图片');
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
