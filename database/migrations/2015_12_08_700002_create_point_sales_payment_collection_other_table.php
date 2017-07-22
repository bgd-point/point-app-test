<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesPaymentCollectionOtherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_payment_collection_other', function ($table) {
            $table->increments('id');

            $table->integer('point_sales_payment_collection_id')->unsigned()->index('point_sales_payment_collection_other_bppo_index');
            $table->foreign('point_sales_payment_collection_id', 'point_sales_payment_collection_other_bppo_foreign')
                ->references('id')->on('point_sales_payment_collection')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index('point_sales_payment_collection_other_coa_index');
            $table->foreign('coa_id', 'point_sales_payment_collection_other_coa_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->text('other_notes');
            $table->decimal('amount', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_payment_collection_other');
    }
}
