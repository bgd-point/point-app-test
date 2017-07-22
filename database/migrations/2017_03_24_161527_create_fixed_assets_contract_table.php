<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFixedAssetsContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_assets_contract', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->integer('journal_id')->unsigned()->index();
            $table->foreign('journal_id')
                ->references('id')->on('journal')
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

            $table->string('code')->unique();
            $table->string('name');
            $table->string('unit');
            $table->string('country');
            $table->decimal('useful_life', 16, 4);
            $table->integer('salvage_value')->unsigned()->index();
            $table->decimal('depreciation', 16, 4);
            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->decimal('total_price', 16, 4);
            $table->decimal('total_paid', 16, 4);
            $table->boolean('disabled')->default(false);

            $table->integer('created_by')->unsigned()->index()->default(1);
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
                
            $table->integer('updated_by')->unsigned()->index()->default(1);
            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fixed_assets_contract');
    }
}
