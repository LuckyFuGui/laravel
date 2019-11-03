<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyCleaningTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_cleaning', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('hour')->comment('项目时常');
            $table->decimal('price',8,2)->comment('价格');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *ßßßß
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_cleaning');
    }
}
