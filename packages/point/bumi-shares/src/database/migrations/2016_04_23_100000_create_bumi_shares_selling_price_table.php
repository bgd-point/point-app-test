<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBumiSharesSellingPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bumi_shares_selling_price', function ($table) {
            $table->increments('id');
            
            $table->integer('shares_id')->unsigned()->index();
            $table->foreign('shares_id')
                ->references('id')->on('bumi_shares')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->double('price', 16, 4);
            $table->nullableTimestamps();
            $table->integer('updated_by')->unsigned()->index();
            $table->foreign('updated_by')
                ->references('id')->on('users')
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
        Schema::drop('bumi_shares_selling_price');
    }
}
