<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPrintCountInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('point_sales_invoice', 'print_count')) {
            Schema::table('point_sales_invoice', function ($table) {
                $table->decimal('print_count', 16, 4);
                $table->tinyInteger('approval_print_status')->default(0); // -1 rejected | 0 pending | 1 approved
                $table->timestamp('approval_print_at')->nullable();
                $table->timestamp('request_approval_print_at')->nullable();
                $table->string('request_approval_print_token')->nullable();
                $table->integer('approval_print_to')->unsigned()->index('point_sales_invoice_approval_print_index');
                $table->foreign('approval_print_to', 'point_sales_invoice_approval_print_foreigns')
                    ->references('id')->on('users')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
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
        if (Schema::hasColumn('point_sales_invoice', 'print_count')) {
            Schema::table('point_sales_invoice', function ($table) {
                $table->dropColumn(['print_count']);
                $table->dropColumn(['approval_print_status']);
                $table->dropColumn(['request_approval_print_at']);
                $table->dropColumn(['request_approval_print_token']);
                $table->dropColumn(['approval_print_to']);
                $table->dropColumn(['approval_print_at']);
            });
        }
    }
}
