<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionOrderReferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_order_reference', function ($table) {
            $table->increments('id');

            $table->integer('expedition_reference_id')->unsigned()->index('point_expedition_order_reference_formulir_index');
            $table->foreign('expedition_reference_id', 'point_expedition_order_reference_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('expedition_order_id')->nullable();
            $table->integer('person_id')->unsigned()->index('point_expedition_order_reference_person_index');
            $table->foreign('person_id', 'point_expedition_order_reference_person_index')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->boolean('include_expedition')->default(false);
            $table->decimal('expedition_fee', 16, 4);

            $table->string('type_of_tax');
            $table->boolean('is_cash')->default(false);
            $table->decimal('subtotal', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->decimal('tax_base', 16, 4);
            $table->decimal('tax', 16, 4);
            $table->decimal('total', 16, 4);
            $table->boolean('finish')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_expedition_order_reference');
    }
}
