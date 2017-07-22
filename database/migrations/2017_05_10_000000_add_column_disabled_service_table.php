<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDisabledServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('service', 'disabled')) {
            Schema::table('service', function ($table) {
                $table->boolean('disabled')->default(false);
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
        if (Schema::hasColumn('service', 'disabled')) {
            Schema::table('service', function ($table) {
                $table->dropColumn(['disabled']);
            });
        }
    }
}
