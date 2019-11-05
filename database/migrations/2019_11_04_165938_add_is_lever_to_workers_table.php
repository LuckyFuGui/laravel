<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLeverToWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->integer('is_leave')->default(0)->comment('是否处于请假中 0:正常上班 1：请假中');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn('is_leave');
        });
    }
}
