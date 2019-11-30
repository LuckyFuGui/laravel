<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('用户ID');
            $table->integer('discount_id')->comment('优惠券ID');
            $table->integer('voucher_type')->comment('优惠卷类型 1：全场保洁劵 2：日常保洁劵 3：新居开荒劵 4：电器清洁劵 5：全家除螨劵');
            $table->decimal('voucher_price',8,2)->comment('优惠卷面值');
            $table->dateTime('use_at')->nullable()->comment('使用时间');
            $table->integer('status')->default(0)->comment('使用状态 0：未使用 1：已使用');
            $table->integer('pay_status')->default(0)->comment('支付状态 0：待支付 1：支付成功 2：支付失败');
            $table->string('pay_sn')->default(null)->comment('系统支付单号');
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
        Schema::dropIfExists('discount_user');
    }
}
