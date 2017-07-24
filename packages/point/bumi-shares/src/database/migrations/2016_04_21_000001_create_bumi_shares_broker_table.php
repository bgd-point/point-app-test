<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBumiSharesBrokerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bumi_shares_broker', function ($table) {
            $table->increments('id');

            $table->string('name')->unique();
            $table->text('notes')->nullable();
            $table->decimal('sales_fee', 16, 4);
            $table->decimal('buy_fee', 16, 4);

            $table->nullableTimestamps();

            $table->boolean('disabled')->default(false);

            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
                
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
        Schema::drop('bumi_shares_broker');
    }
}
