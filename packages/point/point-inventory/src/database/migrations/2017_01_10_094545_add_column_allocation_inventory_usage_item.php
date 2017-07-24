<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAllocationInventoryUsageItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_inventory_usage_item', function ($table) {
            $table->integer('allocation_id')->unsigned()->index('point_inventory_usage_item_allocation_id_index')->default(1);
            $table->foreign('allocation_id', 'point_inventory_usage_item_allocation_id_foreign')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_inventory_usage_item', function ($table) {
            $table->dropForeign('point_inventory_usage_item_allocation_id_foreign');
            $table->dropColumn(['allocation_id']);
        });
    }
}
