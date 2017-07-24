<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_quotation', function ($table) {
            $table->increments('id');

            $table->timestamp('required_date')->useCurrent(); // when they need to buy
            
            $table->integer('formulir_id')->unsigned()->index('point_sales_quotation_formulir_index');
            $table->foreign('formulir_id', 'point_sales_quotation_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index('point_sales_quotation_person_index')->nullable(); // customer
            $table->foreign('person_id', 'point_sales_quotation_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->timestamp('send_mail_at')->nullable(); // when send email to customer

            $table->string('type_of_tax'); // non, include, exclude
            $table->decimal('subtotal', 16, 4); // total value of item
            $table->decimal('discount', 16, 4); // discount of total
            $table->decimal('tax_base', 16, 4); // value for calculate tax
            $table->decimal('tax', 16, 4); // tax value
            $table->decimal('expedition_fee', 16, 4); // additional fee for expedition
            $table->decimal('total', 16, 4); // total payment
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_quotation');
    }
}
