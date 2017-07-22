<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnRawNumberFormulirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('formulir', 'form_raw_number')) {
            Schema::table('formulir', function ($table) {
                $table->integer('form_raw_number')->unsigned()->default(0)->after('form_number');
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
        if (Schema::hasColumn('formulir', 'form_raw_number')) {
            Schema::table('formulir', function ($table) {
                $table->dropColumn(['form_raw_number']);
            });
        }
    }
}
