<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingServiceInvoiceItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_service_invoice_item', function ($table) {
            $table->increments('id');
            
            $table->integer('point_purchasing_service_invoice_id')->unsigned()->index('purchasing_service_invoice_index');
            $table->foreign('point_purchasing_service_invoice_id', 'purchasing_service_invoice_foreign')
                ->references('id')->on('point_purchasing_service_invoice')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('purchasing_service_invoice_item_index');
            $table->foreign('item_id', 'purchasing_service_invoice_item_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->string('unit');
            $table->decimal('converter', 16, 4);
            $table->string('item_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_service_invoice_item');
    }
}
