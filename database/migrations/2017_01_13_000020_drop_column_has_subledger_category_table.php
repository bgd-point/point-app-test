<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnHasSubledgerCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('coa_category', 'has_subledger')) {
            Schema::table('coa_category', function ($table) {
                $table->dropColumn('has_subledger');
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
        if (!Schema::hasColumn('coa_category', 'has_subledger')) {
            Schema::table('coa_category', function ($table) {
                $table->boolean('has_subledger');
            });
        }
    }
}
