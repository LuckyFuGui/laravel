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
            $table->integer('voucher_type')->comment('优惠卷类型');
            $table->decimal('voucher_price',8,2)->comment('优惠卷面值');
            $table->integer('voucher_num')->comment('每组数量');
            $table->decimal('sale_price',8,2)->comment('一组优惠卷总售价');
            $table->decimal('pay_price',8,2)->comment('支付金额');
            $table->dateTime('pay_at')->comment('支付时间');
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
