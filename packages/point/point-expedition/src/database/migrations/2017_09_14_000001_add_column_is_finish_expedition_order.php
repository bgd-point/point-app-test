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
    }
}
