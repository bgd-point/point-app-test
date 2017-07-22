<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFixedAssetsContractDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_assets_contract_detail', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('contract_id')->unsigned()->index();
            $table->foreign('contract_id')
                ->references('id')->on('fixed_assets_contract')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('fixed_assets_contract_reference_id')->unsigned()->index('contract_detail_contract_reference_id_index');
            $table->foreign('fixed_assets_contract_reference_id', 'contract_detail_contract_reference_id_foreign')
                ->references('id')->on('fixed_assets_contract_reference')
                ->onUpdate('cascade')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fixed_assets_contract_detail');
    }
}
