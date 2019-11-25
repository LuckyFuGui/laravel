<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEffectiveDateToDiscountUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discount_user', function (Blueprint $table) {
            $table->dateTime('effective_date')->nullable()->comment('有效时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discount_user', function (Blueprint $table) {
            $table->dropColumn('effective_date');

        });
    }
}
