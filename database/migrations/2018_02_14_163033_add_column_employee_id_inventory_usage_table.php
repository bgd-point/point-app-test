<?php

use Illuminate\Database\Migrations\Migration;

class AddColumnEmployeeIdInventoryUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_inventory_usage', function ($table) {
            $table->unsignedInteger('employee_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_inventory_usage', function ($table) {
            $table->dropColumn(['employee_id']);
        });
    }
}
