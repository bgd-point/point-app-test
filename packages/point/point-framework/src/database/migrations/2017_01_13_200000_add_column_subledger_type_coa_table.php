<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSubledgerTypeCoaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('coa', 'subledger_type')) {
            Schema::table('coa', function ($table) {
                $table->string('subledger_type')->after('has_subledger')->nullable();
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
        if (Schema::hasColumn('coa', 'subledger_type')) {
            Schema::table('coa', function ($table) {
                $table->dropColumn('subledger_type');
            });
        }
    }
}
