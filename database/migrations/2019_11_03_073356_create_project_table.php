<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img')->comment('图片地址');
            $table->string('serverName', 32)->comment('服务名称');
            $table->string('company', 8)->comment('单位');
            $table->decimal('price', 8, 2)->comment('价格');
            $table->integer('branch')->comment('分钟');
            $table->tinyInteger('type')->comment('类型:1电器清洁、2全家除螨');
            $table->tinyInteger('state')->comment('状态：0下架、1上架');
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
        Schema::dropIfExists('project');
    }
}
