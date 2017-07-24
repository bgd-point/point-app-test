<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesServiceInvoiceServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_service_invoice_service', function ($table) {
            $table->increments('id');
            
            $table->integer('point_sales_service_invoice_id')->unsigned()->index('ps_service_invoice_id_index');
            $table->foreign('point_sales_service_invoice_id', 'ps_service_invoice_id_foreign')
                ->references('id')->on('point_sales_service_invoice')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('service_id')->unsigned();
            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->string('service_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_service_invoice_service');
    }
}
