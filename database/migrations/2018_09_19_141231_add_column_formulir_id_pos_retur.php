<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFormulirIdPosRetur extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sales_pos_retur', function (Blueprint $table) {
            $table->integer('formulir_id')->unsigned();
            $table->foreign('formulir_id')
                  ->references('id')
                  ->on('formulir');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_sales_pos_retur', function (Blueprint $table) {
            $table->dropForeign(['formulir_id']);
            $table->dropColumn('formulir_id');
        });
    }
}
