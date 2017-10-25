<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDoneExpeditionOrderTable extends Migration
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
        if (!Schema::hasColumn('point_expedition_order', 'done')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->boolean('done')->nullable()->default(false);
            });
        }

        if (!Schema::hasColumn('point_expedition_order', 'group')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->integer('group')->unsigned();
            });
        }

        if (!Schema::hasColumn('point_expedition_order', 'form_reference_id')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->integer('form_reference_id')->unsigned()->index('point_expedition_order_form_ref_id_index');
                $table->foreign('form_reference_id', 'point_expedition_order_form_ref_id_foreign')
                    ->references('id')->on('formulir')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }


        // DROP COLUMN
        if (Schema::hasColumn('point_expedition_order_reference', 'expedition_order_id')) {
            Schema::table('point_expedition_order_reference', function ($table) {
                $table->dropColumn(['expedition_order_id']);
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
        if (Schema::hasColumn('point_expedition_order', 'done')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->dropColumn(['done']);
            });
        }

        if (Schema::hasColumn('point_expedition_order', 'group')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->dropColumn(['group']);
            });
        }

        if (Schema::hasColumn('point_expedition_order', 'form_reference_id')) {
            Schema::table('point_expedition_order', function ($table) {
                $table->dropForeign('point_expedition_order_form_ref_id_foreign');
                $table->dropIndex('point_expedition_order_form_ref_id_index');
                $table->dropColumn(['form_reference_id']);
            });
        }

        if (!Schema::hasColumn('point_expedition_order_reference', 'expedition_order_id')) {
            Schema::table('point_expedition_order_reference', function ($table) {
                $table->integer('expedition_order_id')->nullable();
            });
        }
    }
}
