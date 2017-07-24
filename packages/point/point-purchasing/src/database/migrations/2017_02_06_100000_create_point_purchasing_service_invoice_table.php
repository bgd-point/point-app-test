<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingServiceInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_service_invoice', function ($table) {
            $table->increments('id');

            $table->timestamp('due_date')->useCurrent();
            
            $table->integer('formulir_id')->unsigned()->index('purchasing_service_invoice_formulir_index');
            $table->foreign('formulir_id', 'purchasing_service_invoice_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index('point_purchasing_service_person_index');
            $table->foreign('person_id', 'purchasing_service_invoice_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('subtotal', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->decimal('tax_base', 16, 4);
            $table->decimal('tax', 16, 4);
            $table->string('type_of_tax')->index()->nullable();
            $table->decimal('total', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_service_invoice');
    }
}
