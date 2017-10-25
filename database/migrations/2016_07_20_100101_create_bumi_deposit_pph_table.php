<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBumiDepositPphTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bumi_deposit_pph', function ($table) {
            $table->increments('id');

            $table->integer('bumi_deposit_id')->unsigned()->index('bumi_deposit_pph_deposit_index');
            $table->foreign('bumi_deposit_id', 'bumi_deposit_pph_deposit_foreign')
                ->references('id')->on('bumi_deposit')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamp('pph_date')->useCurrent(); // when it finish
            $table->boolean('pph_received')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bumi_deposit_pph');
    }
}
