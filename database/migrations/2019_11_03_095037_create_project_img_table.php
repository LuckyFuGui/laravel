<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectImgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_img', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('project_id')->comment('项目ID，1：日常保洁；2：电器清洁；3：全家除螨；4：新居开荒');
            $table->tinyInteger('type_id')->comment('1:banner;2:服务介绍；3：资费说明；4：服务范围');
            $table->string('img')->comment('图片链接');
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
        Schema::dropIfExists('project_img');
    }
}
