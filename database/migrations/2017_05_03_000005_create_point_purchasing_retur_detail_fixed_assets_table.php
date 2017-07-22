<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingReturDetailFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_retur_detail', function ($table) {
            $table->increments('id');

            $table->integer('point_purchasing_retur_id')->unsigned()->index('point_purchasing_fa_retur_detail_index');
            $table->foreign('point_purchasing_retur_id', 'point_purchasing_fa_retur_detail_foreign')
                ->references('id')->on('point_purchasing_fixed_assets_retur')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('allocation_id')->unsigned()->index('point_purchasing_fa_retur_detail_allocation_index');
            $table->foreign('allocation_id', 'point_purchasing_fa_retur_detail_allocation_foreign')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('name');
            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->string('unit');
            $table->decimal('converter', 16, 4);
            $table->string('item_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_fixed_assets_retur_detail');
    }
}
