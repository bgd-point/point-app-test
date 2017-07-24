<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFixedAssetsContractReferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema::create('fixed_assets_contract_reference', function($table)
        {
            $table->increments('id');

            $table->integer('fixed_assets_contract_id')->nullable()->unsigned();
            $table->integer('form_reference_id')->unsigned()->index('fixed_assets_contract_formulir_reference_index');
            $table->foreign('form_reference_id', 'fixed_assets_contract_formulir_reference_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('journal_id')->unsigned()->index();
            $table->foreign('journal_id')
                ->references('id')->on('journal')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('supplier_id')->unsigned()->index();
            $table->foreign('supplier_id')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->timestamp('date_purchased')->useCurrent();
            $table->string('name');
            $table->string('unit');
            $table->string('country');

            $table->decimal('total_paid',16,4);
            $table->decimal('depreciation',16,4);
            $table->decimal('quantity',16,4);
            $table->decimal('discount', 16, 4);
            $table->decimal('price',16,4);
            $table->decimal('total_price',16,4);
            $table->decimal('useful_life',16,4);
            $table->decimal('salvage_value',16,4);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fixed_assets_contract_reference');
    }
}
