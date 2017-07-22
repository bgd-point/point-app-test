<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBumiDepositBankAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bumi_deposit_bank_account', function ($table) {
            $table->increments('id');

            $table->integer('bumi_deposit_bank_id')->unsigned()->index('bumi_deposit_bank_account_bank_index');
            $table->foreign('bumi_deposit_bank_id', 'bumi_deposit_bank_account_bank_foreign')
                ->references('id')->on('bumi_deposit_bank')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->text('account_notes')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bumi_deposit_bank_account');
    }
}
