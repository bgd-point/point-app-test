<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMoneyReceivedPointSalesPosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sales_pos', function ($table) {
            if (!Schema::hasColumn('point_sales_pos', 'money_received')) {
                $table->decimal('money_received', 16, 4);
            }
            if (!Schema::hasColumn('point_sales_pos', 'warehouse_id')) {
                $table->integer('warehouse_id')->unsigned()->index('point_sales_pos_warehouse_index');
                $table->foreign('warehouse_id', 'point_sales_pos_warehouse_foreign')
                    ->references('id')->on('warehouse')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('point_sales_pos', 'money_received')) {
            Schema::table('point_sales_pos', function ($table) {
                $table->dropColumn(['money_received']);
            });
        }
        if (Schema::hasColumn('point_sales_pos', 'warehouse_id')) {
            Schema::table('point_sales_pos', function ($table) {
                $table->dropForeign('point_sales_pos_warehouse_foreign');
                $table->dropColumn(['warehouse_id']);
            });
        }
    }
}
