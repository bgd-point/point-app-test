<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingInvoiceItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_invoice_item', function ($table) {
            $table->increments('id');
            
            $table->integer('point_purchasing_invoice_id')->unsigned()->index('point_purchasing_invoice_item_bpi_index');
            $table->foreign('point_purchasing_invoice_id', 'point_purchasing_invoice_item_bpi_foreign')
                ->references('id')->on('point_purchasing_invoice')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('point_purchasing_invoice_item_item_index');
            $table->foreign('item_id', 'point_purchasing_invoice_item_item_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('allocation_id')->unsigned()->index('point_purchasing_invoice_item_allocation_index');
            $table->foreign('allocation_id', 'point_purchasing_invoice_item_allocation_foreign')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->string('unit');
            $table->decimal('converter', 16, 4);
            $table->string('item_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_invoice_item');
    }
}
