<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkersDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workers_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('worker_id')->comment('员工ID');
            $table->integer('month_times')->comment('本月服务次数');
            $table->decimal('month_price',12,4)->comment('本月完成订单金额');
            $table->decimal('month_evaluate',8,2)->comment('近30天评价');
            $table->integer('waiting_at')->comment('本月待岗时长');
            $table->integer('times')->comment('累计服务次数');
            $table->decimal('price',12,4)->comment('累计订单金额');
            $table->decimal('evaluate',8,2)->comment('累计评价');
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
        Schema::dropIfExists('workers_details');
    }
}
