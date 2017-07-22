<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnCutOffFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('point_accounting_cut_off_fixed_assets_detail', 'depreciation')) {
            Schema::table('point_accounting_cut_off_fixed_assets_detail', function ($table) {
                $table->dropColumn('depreciation');
            });
        }

        if (Schema::hasColumn('point_accounting_cut_off_fixed_assets_detail', 'period')) {
            Schema::table('point_accounting_cut_off_fixed_assets_detail', function ($table) {
                $table->dropColumn('period');
            });
        }

        if (Schema::hasColumn('point_accounting_cut_off_fixed_assets_detail', 'salvage_value')) {
            Schema::table('point_accounting_cut_off_fixed_assets_detail', function ($table) {
                $table->dropColumn('salvage_value');
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
        if (!Schema::hasColumn('point_accounting_cut_off_fixed_assets_detail', 'depreciation')) {
            Schema::table('point_accounting_cut_off_fixed_assets_detail', function ($table) {
                $table->decimal('depreciation', 16, 4);
            });
        }

        if (!Schema::hasColumn('point_accounting_cut_off_fixed_assets_detail', 'period')) {
            Schema::table('point_accounting_cut_off_fixed_assets_detail', function ($table) {
                $table->decimal('period', 16, 4);
            });
        }

        if (!Schema::hasColumn('point_accounting_cut_off_fixed_assets_detail', 'salvage_value')) {
            Schema::table('point_accounting_cut_off_fixed_assets_detail', function ($table) {
                $table->decimal('salvage_value', 16, 4);
            });
        }
    }
}
