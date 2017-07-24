<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountDepreciationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_depreciation', function ($table) {
            $table->increments('id');

            $table->integer('account_fixed_asset_id')->unsigned()->index();
            $table->foreign('account_fixed_asset_id')
                ->references('id')->on('coa')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('account_depreciation_id')->unsigned()->nullable()->index();
            $table->foreign('account_depreciation_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('account_depreciation');
    }
}
