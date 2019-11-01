<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('用户ID');
            $table->string('img')->comment('头像');
            $table->string('project_ids')->comment('服务项目ID');
            $table->string('name')->comment('姓名');
            $table->tinyInteger('sex')->comment('性别');
            $table->integer('phone')->comment('手机号');
            $table->integer('status')->default(1)->comment('状态 1：在岗 2：离职');
            $table->date('entry_at')->comment('入职时间');
            $table->date('quit_at')->comment('离职时间');
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
        Schema::dropIfExists('workers');
    }
}
