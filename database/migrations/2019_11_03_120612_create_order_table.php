<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('名称');
            $table->string('phone')->comment('手机');
            $table->string('address')->comment('目标地址');
            $table->string('comment')->comment('详细标注');
            $table->string('start_time')->comment('开始时间');
            $table->string('end_time')->comment('结束时间');
            $table->integer('cid')->comment('优惠卷id');
            $table->decimal('coupon', 8, 2)->comment('优惠卷抵扣费用');
            $table->decimal('special', 8, 2)->comment('特殊费用');
            $table->decimal('payment', 8, 2)->comment('实际支付');
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
        Schema::dropIfExists('order');
    }
}
