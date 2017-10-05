<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsFinishExpeditionOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ADD COLUMN
        if (!Schema::hasColumn('point_expedition_order', 'is_finish')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->boolean('is_finish')->nullable()->default(false);
            });
        }

        if (!Schema::hasColumn('point_expedition_order_item', 'discount')) {
            Schema::table('point_expedition_order_item', function ($table) {
                $table->decimal('discount', 16, 4);
            });
        }

        if (!Schema::hasColumn('point_expedition_invoice_item', 'discount')) {
            Schema::table('point_expedition_invoice_item', function ($table) {
                $table->decimal('discount', 16, 4);
                $table->decimal('price', 16, 4);
            });
        }

        if (!Schema::hasColumn('point_expedition_invoice', 'is_reset_journal')) {
            Schema::table('point_expedition_invoice', function ($table) {
                $table->boolean('is_reset_journal')->nullable()->default(false); // reset journal for tracking if invoice rejournal from expedition order, 1 = yes, 0 = no
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('point_expedition_order', 'is_finish')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->dropColumn(['is_finish']);
            });
        }

        if (Schema::hasColumn('point_expedition_order_item', 'discount')) {
            Schema::table('point_expedition_order_item', function ($table) {
                $table->dropColumn(['discount']);
            });
        }

        if (Schema::hasColumn('point_expedition_invoice', 'is_reset_journal')) {
            Schema::table('point_expedition_invoice', function ($table) {
                $table->dropColumn(['is_reset_journal']);
            });
        }

        if (Schema::hasColumn('point_expedition_invoice_item', 'discount')) {
            Schema::table('point_expedition_invoice_item', function ($table) {
                $table->dropColumn(['discount']);
                $table->dropColumn(['price']);
            });
        }
    }
}
