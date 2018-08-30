<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserWarehouseAllowNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_warehouse', function (Blueprint $table) {
            //Or disable foreign check with: 
            //Schema::disableForeignKeyConstraints();
            $table->dropForeign('user_warehouse_warehouse_id_foreign');
            $table->integer('warehouse_id')->nullable()->unsigned()->change();
            //Remove the following line if disable foreign key
            $table->foreign('warehouse_id')->references('id')->on('warehouse');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_warehouse', function (Blueprint $table) {
            //Or disable foreign check with: 
            //Schema::disableForeignKeyConstraints();
            $table->dropForeign('user_warehouse_warehouse_id_foreign');
        });
        Schema::table('user_warehouse', function (Blueprint $table) {
            $table->integer('warehouse_id')->nullable(false)->unsigned()->change();
            //Remove the following line if disable foreign key
            $table->foreign('warehouse_id')->references('id')->on('warehouse');
        });
    }
}
