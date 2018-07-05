<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RequestDeleteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formulir', function($table) {
            $table->string('cancel_token');
            $table->timestamp('cancel_requested_at')->nullable();
            $table->timestamp('cancel_rejected_at')->nullable();
            $table->tinyInteger('cancel_request_status')->nullable(); // 0 = pending, 1 = accepted, -1 = rejected
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formulir', function($table) {
            $table->dropColumn('cancel_token');
            $table->dropColumn('cancel_requested_at');
            $table->dropColumn('cancel_rejected_at');
            $table->dropColumn('cancel_request_status');
        });
    }
}
