<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item', function ($table) {
            $table->increments('id');

            // Services or stock
            $table->integer('item_type_id')->unsigned()->index()->nullable();
            $table->foreign('item_type_id')
                ->references('id')->on('item_type')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            // Category for each item, it will generate code based on code in category
            $table->integer('item_category_id')->unsigned()->index()->nullable();
            $table->foreign('item_category_id')
                ->references('id')->on('item_category')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            // Link account
            $table->integer('account_asset_id')->unsigned()->index();
            $table->foreign('account_asset_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('barcode')->unique()->nullable();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->text('notes')->nullable();

            // Only related for item type = stock
            $table->decimal('reminder_quantity_minimum', 15, 4)->unsigned()->default(0);
            $table->boolean('reminder')->default(false);

            $table->nullableTimestamps();

            // Item cannot be deleted if already used, if you want to remove it you can hide it here
            $table->boolean('disabled')->default(false);

            $table->integer('created_by')->unsigned()->index()->default(1);
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
                
            $table->integer('updated_by')->unsigned()->index()->default(1);
            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('item');
    }
}
