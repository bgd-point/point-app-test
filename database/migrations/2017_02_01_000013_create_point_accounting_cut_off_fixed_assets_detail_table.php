<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointAccountingCutOffFixedAssetsDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema::create('point_accounting_cut_off_fixed_assets_detail', function($table)
        {
            $table->increments('id');
            
            $table->integer('fixed_assets_id')->unsigned()->index('point_accounting_fixed_assets_id_index');
            $table->foreign('fixed_assets_id', 'point_accounting_fixed_assets_id_foreign')
                ->references('id')->on('point_accounting_cut_off_fixed_assets')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->timestamp('date_purchased')->useCurrent();

            $table->integer('supplier_id')->unsigned()->index();
            $table->foreign('supplier_id')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->string('name');
            $table->string('country');

            $table->decimal('total_paid',16,4);
            $table->decimal('depreciation',16,4);
            $table->decimal('quantity',16,4);
            $table->decimal('price',16,4);
            $table->decimal('total_price',16,4);
            $table->decimal('period',16,4);
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
        Schema::drop('point_accounting_cut_off_fixed_assets_detail');
    }
}
