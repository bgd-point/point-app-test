<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPurchasingInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('point_purchasing_invoice', 'is_reset_journal')) {
            Schema::table('point_purchasing_invoice', function ($table) {
                $table->boolean('is_reset_journal')->default(false); // to check is invoice remove journal from good received.
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
        if (Schema::hasColumn('point_purchasing_invoice', 'is_reset_journal')) {
            Schema::table('point_purchasing_invoice', function ($table) {
                $table->dropColumn(['is_reset_journal']);
            });
        }
    }
}
