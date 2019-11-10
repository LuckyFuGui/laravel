<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_id')->comment('订单ID');
            $table->integer('worker_id')->comment('员工ID');
            $table->integer('is_later')->comment('是否迟到');
            $table->integer('is_quiet')->comment('是否安静');
            $table->integer('score')->comment('综合评分');
            $table->integer('attitude')->comment('服务态度');
            $table->string('remark')->default('')->comment('备注');
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
        Schema::dropIfExists('comment');
    }
}
