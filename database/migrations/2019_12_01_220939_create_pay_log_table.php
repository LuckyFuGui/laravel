<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_log', function (Blueprint $table) {
            $table->bigIncrements('pay_id');
            $table->string('appid',32)->comment('公众账号ID');
            $table->string('attach',32)->comment('商家名称');
            $table->string('bank_type',16)->comment('类型');
            $table->string('cash_fee',16)->comment('金额');
            $table->string('fee_type',16)->comment('支付类型');
            $table->string('id')->comment('订单id');
            $table->string('is_subscribe',16)->comment('***');
            $table->string('mch_id',16)->comment('微信支付分配的商户号');
            $table->string('nonce_str',32)->comment('随机字符串');
            $table->string('openid',32)->comment('支付人openid');
            $table->string('out_trade_no',16)->comment('商户订单号');
            $table->string('result_code',16)->comment('返回码');
            $table->string('return_code',16)->comment('返回参数');
            $table->string('sign')->comment('支付签名');
            $table->string('time_end')->comment('支付最后时间');
            $table->string('total_fee')->comment('支付价格：分');
            $table->string('trade_type')->comment('支付类型');
            $table->string('transaction_id')->comment('支付单号');
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
        Schema::dropIfExists('pay_log');
    }
}
