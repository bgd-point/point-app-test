<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCutoffReferenceSalesDownpaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('point_sales_downpayment', 'cutoff_account_id')) {
            Schema::table('point_sales_downpayment', function ($table) {
                $table->integer('cutoff_account_id')->unsigned()->default(0);
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
        // SERVICE SALES
        if (Schema::hasColumn('point_sales_downpayment', 'cutoff_account_id')) {
            Schema::table('point_sales_downpayment', function ($table) {
                $table->dropColumn(['cutoff_account_id']);
            });
        }
    }
}
