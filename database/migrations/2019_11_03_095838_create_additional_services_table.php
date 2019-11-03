<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->default(1)->comment('项目ID，默认1：日常保洁');
            $table->string('services_name')->comment('服务名称');
            $table->decimal('services_price',8,2)->comment('服务价格');
            $table->tinyInteger('services_status')->default(1)->comment('服务状态 1：上架；2：下架');
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
        Schema::dropIfExists('additional_services');
    }
}
