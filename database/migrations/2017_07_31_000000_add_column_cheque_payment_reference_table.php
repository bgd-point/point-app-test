<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnChequePaymentReferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('point_finance_payment_reference', 'bank_name')) {
            Schema::table('point_finance_payment_reference', function ($table) {
                $table->string('bank_name')->nullable();
                $table->timestamp('due_date')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('point_finance_payment_reference', 'bank_name')) {
            Schema::table('point_finance_payment_reference', function ($table) {
                $table->dropColumn('bank_name');
                $table->dropColumn('due_date');
            });
        }
    }
}
