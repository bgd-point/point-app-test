<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingServiceInvoiceServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_service_invoice_service', function ($table) {
            $table->increments('id');
            
            $table->integer('point_purchasing_service_invoice_id')->unsigned()->index('purchasing_service_invoice_id_index');
            $table->foreign('point_purchasing_service_invoice_id', 'purchasing_service_invoice_id_foreign')
                ->references('id')->on('point_purchasing_service_invoice')
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
        Schema::drop('point_purchasing_service_invoice_service');
    }
}
