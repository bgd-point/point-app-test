<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class ChangeDiscountPrecision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_purchasing_order', function (Blueprint $table) {
            $table->decimal('discount', 28, 16)->change();
        });
        Schema::table('point_purchasing_invoice', function (Blueprint $table) {
            $table->decimal('discount', 28, 16)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_purchasing_order', function (Blueprint $table) {
            $table->decimal('discount', 16, 4)->change();
        });
        Schema::table('point_purchasing_invoice', function (Blueprint $table) {
            $table->decimal('discount', 16, 4)->change();
        });
    }
}
