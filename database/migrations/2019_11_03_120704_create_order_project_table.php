<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_project', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pid')->comment('项目id');
            $table->integer('oid')->comment('订单id');
            $table->string('name')->comment('项目名称');
            $table->decimal('price', 8, 2)->comment('价格');
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
        Schema::dropIfExists('order_project');
    }
}
