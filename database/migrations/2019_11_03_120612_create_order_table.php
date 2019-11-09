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
            $table->string('order_sn')->comment('订单编号');
            $table->integer('uid')->comment('用户id');
            $table->integer('sid')->default(0)->comment('服务id');
            $table->string('name')->default('')->comment('名称');
            $table->string('phone')->default('')->comment('手机');
            $table->string('address')->default('')->comment('目标地址');
            $table->string('comment')->default('')->comment('详细标注');
            $table->dateTime('start_time')->comment('开始时间');
            $table->dateTime('end_time')->comment('结束时间');
            $table->integer('cid')->default(0)->comment('优惠卷id');
            $table->decimal('coupon', 8, 2)->default(0)->comment('优惠卷抵扣费用');
            $table->decimal('special', 8, 2)->default(0)->comment('特殊费用');
            $table->decimal('payment', 8, 2)->default(0)->comment('实际支付');
            $table->integer('pay_type')->default(0)->comment('状态:0已下单，1已付款，2客人已取消，3到期自动废弃，4已完成');
            $table->integer('pl')->default(0)->comment('是否评论');
            $table->integer('server_type')->comment('服务类型');
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
