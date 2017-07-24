<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddColumnAccountWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('warehouse', 'petty_cash_account')) {
            Schema::table('warehouse', function ($table) {
                $table->integer('petty_cash_account')->unsigned()->index();
                $table->foreign('petty_cash_account')
                    ->references('id')->on('coa')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
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
        if (Schema::hasColumn('warehouse', 'petty_cash_account')) {
            try {
                Schema::table('warehouse', function ($table) {
                    $table->dropColumn('petty_cash_account');
                });
            } catch (\Exception $e) {
                Schema::table('warehouse', function ($table) {
                    $table->dropForeign('warehouse_petty_cash_account_foreign');
                    $table->dropColumn('petty_cash_account');
                });
            }
        }
    }
}
