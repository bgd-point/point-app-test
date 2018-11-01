<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCashOuted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_finance_cash_advance', function (Blueprint $table) {
            $table->boolean('handed_over')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_finance_cash_advance', function (Blueprint $table) {
            $table->dropColumn('handed_over');
        });
    }
}
