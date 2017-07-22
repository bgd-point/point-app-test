<?php

use Illuminate\Database\Migrations\Migration;

class AddColumnGroupTypePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function ($table) {
            $table->string('group');
            $table->string('type');
            $table->string('action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function ($table) {
            $table->dropColumn(['group', 'type', 'action']);
        });
    }
}
