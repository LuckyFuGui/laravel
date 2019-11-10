<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('status')->default(0)->comment('活动状态 0:待开始 1：进行中 2：已结束 3：已取消');
            $table->integer('type')->default(1)->comment('活动类型 1：代金劵');
            $table->integer('uid')->comment('管理员ID');
            $table->dateTime('begin_at')->nullable()->comment('开始时间');
            $table->dateTime('end_at')->nullable()->comment('结束时间');
            $table->integer('voucher_type')->comment('代金劵类型 1：全场保洁劵 2：日常保洁劵 3：新居开荒劵 4：电器清洁劵 5：全家除螨劵');
            $table->decimal('voucher_price',8,2)->comment('每张代金劵面值');
            $table->integer('voucher_num')->comment('每组代金劵数量');
            $table->decimal('sale_price',8,2)->default(0)->comment('每组售价');
            $table->integer('salable_num')->comment('可售数量');
            $table->integer('sold_num')->comment('已售数量');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discount');
    }
}
