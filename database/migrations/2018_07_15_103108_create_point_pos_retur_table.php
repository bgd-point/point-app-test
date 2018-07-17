<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPosReturTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_pos_retur', function (Blueprint $table) {
            $table->increments('id');

            $table->timestamp('form_date')->useCurrent();

            $table->integer('pos_id')->unsigned()->index('point_sales_pos_retur_pos_index');
            $table->foreign('pos_id', 'point_sales_pos_retur_pos_foreign')
                ->references('id')->on('point_sales_pos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('customer_id')->unsigned()->index('point_sales_pos_retur_customer_index');
            $table->foreign('customer_id', 'point_sales_pos_retur_customer_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('total', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_pos_retur');
    }
}
