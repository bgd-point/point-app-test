<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFixedAssetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_asset', function ($table) {
            $table->increments('id');

            $table->integer('account_id')->unsigned()->index();
            $table->foreign('account_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');
                
            $table->integer('useful_life')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fixed_asset');
    }
}
