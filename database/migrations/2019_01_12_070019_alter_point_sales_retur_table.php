<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterPointSalesReturTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sales_retur', function (Blueprint $table) {
            $table->unsignedInteger('point_sales_invoice_id');

            $table->foreign('point_sales_invoice_id')
                ->references('id')->on('point_sales_invoice')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_sales_retur', function (Blueprint $table) {
            $table->dropColumn('point_sales_invoice_id');
        });
    }
}
