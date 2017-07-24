<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBumiSharesStockFifoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bumi_shares_stock_fifo', function ($table) {
            $table->increments('id');
            
            $table->integer('shares_in_id')->unsigned()->index();
            $table->foreign('shares_in_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('shares_out_id')->unsigned()->index();
            $table->foreign('shares_out_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->double('average_price', 16, 4);
            $table->double('price', 16, 4);
            $table->double('quantity', 16, 4);
            $table->text('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bumi_shares_stock_fifo');
    }
}
