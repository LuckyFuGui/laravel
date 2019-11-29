<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderErrorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_error', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('oid')->comment('订单id');
            $table->string('content')->default('***')->comment('取消订单原因');
            $table->integer('type')->default(1)->comment('类型：自己或者用户');
            $table->integer('uid')->default(0)->comment('操作者id');
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
        Schema::dropIfExists('order_error');
    }
}
