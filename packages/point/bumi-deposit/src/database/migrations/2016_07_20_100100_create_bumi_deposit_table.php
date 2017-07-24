<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBumiDepositTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bumi_deposit', function ($table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index('bumi_deposit_formulir_index');
            $table->foreign('formulir_id', 'bumi_deposit_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('reference_deposit_id')->nullable()->unsigned()->index('bumi_deposit_reference_index')->default(null);
            $table->foreign('reference_deposit_id', 'bumi_deposit_reference_foreign')
                ->references('id')->on('bumi_deposit')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('deposit_bank_id')->unsigned()->index('bumi_deposit_bank_index');
            $table->foreign('deposit_bank_id', 'bumi_deposit_bank_foreign')
                ->references('id')->on('bumi_deposit_bank')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('deposit_bank_account_id')->unsigned()->index('bumi_deposit_bank_account_index');
            $table->foreign('deposit_bank_account_id', 'bumi_deposit_bank_account_foreign')
                ->references('id')->on('bumi_deposit_bank_account')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('deposit_bank_product_id')->unsigned()->index('bumi_deposit_bank_product_index');
            $table->foreign('deposit_bank_product_id', 'bumi_deposit_bank_product_foreign')
                ->references('id')->on('bumi_deposit_bank_product')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('deposit_group_id')->unsigned()->index('bumi_deposit_group_index');
            $table->foreign('deposit_group_id', 'bumi_deposit_group_foreign')
                ->references('id')->on('bumi_deposit_group')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('deposit_owner_id')->unsigned()->index('bumi_deposit_owner_index');
            $table->foreign('deposit_owner_id', 'bumi_deposit_owner_foreign')
                ->references('id')->on('bumi_deposit_owner')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('deposit_category_id')->unsigned()->index('bumi_deposit_category_index');
            $table->foreign('deposit_category_id', 'bumi_deposit_category_foreign')
                ->references('id')->on('bumi_deposit_category')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('deposit_number'); // you get this number from your bank when make a deposit
            $table->double('deposit_time'); // how many days
            $table->timestamp('due_date')->useCurrent(); // when it finish
            $table->double('original_amount', 16, 4);
            $table->double('interest_percent', 16, 4);
            $table->double('interest_value', 16, 4);
            $table->double('tax_percent', 16, 4);
            $table->double('tax_value', 16, 4);
            $table->double('total_amount', 16, 4);
            $table->double('total_interest', 16, 4);
            $table->double('total_days_in_year', 16, 4);

            $table->timestamp('withdraw_date');
            $table->double('withdraw_amount', 16, 4);
            $table->tinyInteger('withdraw_approval_status')->default(0);
            $table->text('withdraw_approval_message')->nullable();
            $table->timestamp('withdraw_approval_at')->nullable();
            $table->integer('withdraw_approval_to')->unsigned()->nullable()->index();
            $table->foreign('withdraw_approval_to')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->text('withdraw_notes');

            $table->text('important_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bumi_deposit');
    }
}
