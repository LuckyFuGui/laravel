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
