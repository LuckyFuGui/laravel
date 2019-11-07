<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('worker_id')->comment('员工ID');
            $table->dateTime('begin_at')->comment('请假开始时间');
            $table->dateTime('end_at')->comment('请假结束时间');
            $table->integer('status')->default(0)->comment('当前请假状态：0:当前请假还没开始；1:正在进行中；2:正在进行中已取消；3还未开始已取消；4:已结束');
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
        Schema::dropIfExists('leave_log');
    }
}
