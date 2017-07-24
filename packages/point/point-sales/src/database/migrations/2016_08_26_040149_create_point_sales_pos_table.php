<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesPosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_pos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('formulir_id')->unsigned()->index('point_sales_pos_formulir_index');
            $table->foreign('formulir_id', 'point_sales_pos_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('customer_id')->unsigned()->index('point_sales_pos_customer_index');
            $table->foreign('customer_id', 'point_sales_pos_customer_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('subtotal', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->decimal('tax_base', 16, 4)->default(0);
            $table->decimal('tax', 16, 4)->default(0);
            $table->decimal('total', 16, 4);

            $table->string('tax_type')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_pos');
    }
}
