<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllocationIdToJournalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('journal')) {
            Schema::table('journal', function (Blueprint $table) {
                if (!Schema::hasColumn('journal', 'allocation_id')) {
                    $table->integer('allocation_id')->unsigned()->nullable()->after('subledger_type');
                    $table->foreign('allocation_id', 'journal_allocation_id_foreign')
                        ->references('id')->on('allocation')
                        ->onUpdate('restrict')
                        ->onDelete('restrict');
                }
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
        if (Schema::hasTable('journal')) {
            Schema::table('journal', function (Blueprint $table) {
                if (Schema::hasColumn('journal', 'allocation_id')) {
                    $table->dropForeign('journal_allocation_id_foreign');
                    $table->dropColumn('allocation_id');
                }
            });
        }
    }
}
