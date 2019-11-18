<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('用户id');
            $table->string('name')->comment('名称');
            $table->string('phone')->comment('手机');
            $table->string('address')->comment('地址');
            $table->string('comment')->comment('详情');
            $table->string('pattern')->comment('建筑描述：居室');
            $table->string('plane')->comment('建筑描述：面积');
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
        Schema::dropIfExists('address');
    }
}
