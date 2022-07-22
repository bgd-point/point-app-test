<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Point\Framework\Helpers\JournalHelper;

class AddColumnCoaInventoryUsageItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $inventory_usage_expense_account = JournalHelper::getAccount('point inventory usage', 'inventory differences');

        Schema::table('point_inventory_usage_item', function ($table) use ($inventory_usage_expense_account) {
            $table->unsignedInteger('coa_id')->default($inventory_usage_expense_account);
            $table->foreign('coa_id')->references('id')->on('coa')->onDelete('restrict');
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
            $table->dropForeign(['coa_id']);
            $table->dropColumn(['coa_id']);
        });
    }
}
