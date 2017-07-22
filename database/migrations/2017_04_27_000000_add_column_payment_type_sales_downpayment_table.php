<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPaymentTypeSalesDownpaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('point_sales_downpayment', 'payment_type')) {
            Schema::table('point_sales_downpayment', function ($table) {
                $table->string('payment_type')->nullable()->default('bank');
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
        if (Schema::hasColumn('point_sales_downpayment', 'payment_type')) {
            Schema::table('point_sales_downpayment', function ($table) {
                $table->dropColumn(['payment_type']);
            });
        }
    }
}
