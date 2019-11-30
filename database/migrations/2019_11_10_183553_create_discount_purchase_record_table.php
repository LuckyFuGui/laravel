<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountPurchaseRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_purchase_record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('用户ID');
            $table->integer('discount_id')->comment('优惠券ID');
            $table->integer('voucher_type')->comment('优惠卷类型');
            $table->decimal('voucher_price',8,2)->comment('优惠卷面值');
            $table->integer('voucher_num')->comment('每组数量');
            $table->decimal('sale_price',8,2)->comment('一组优惠卷总售价');
            $table->decimal('pay_price',8,2)->default(0)->comment('支付金额');
            $table->integer('pay_status')->default(0)->comment('支付状态 0：待支付 1：支付成功 2：支付失败');
            $table->string('pay_sn')->default(null)->comment('系统支付单号');
            $table->string('wx_sn')->default(null)->comment('微信支付单号,用于查询订单');
            $table->dateTime('pay_at')->nullable()->comment('支付时间');
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
        Schema::dropIfExists('discount_purchase_record');
    }
}
