<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_invoice', function ($table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index('point_expedition_invoice_formulir_index');
            $table->foreign('formulir_id', 'point_expedition_invoice_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('expedition_id')->unsigned()->index('point_expedition_invoice_expedition_index');
            $table->foreign('expedition_id', 'point_expedition_invoice_expedition_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('subtotal', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->decimal('tax_base', 16, 4);
            $table->decimal('tax', 16, 4);
            $table->decimal('total', 16, 4);
            $table->integer('type_of_fee')->index()->nullable();
            $table->string('type_of_tax')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_expedition_invoice');
    }
}
