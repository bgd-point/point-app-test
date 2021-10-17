<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_report', function ($table) {
            $table->increments('id');

            $table->datetime("date_start");
            $table->datetime("date_end");
            $table->string("item_code");
            $table->string("item_name");
            $table->datetime("stock_opname_date")->nullable();
            $table->string('stock_opname_reference');
            $table->decimal('quantity_opname', 16, 4);
            $table->decimal('quantity_start', 16, 4);
            $table->decimal('quantity_in', 16, 4);
            $table->decimal('quantity_out', 16, 4);
            $table->decimal('quantity_end', 16, 4);
            $table->string('last_buy_reference')->nullable();
            $table->decimal('last_buy_price', 16, 4)->default(0);
            $table->timestamp("created_at");
            $table->timestamp("updated_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('inventory_report');
    }
}
