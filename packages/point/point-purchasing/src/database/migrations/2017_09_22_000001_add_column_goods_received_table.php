<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnGoodsReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('point_purchasing_goods_received', 'type_of_tax')) {
            Schema::table('point_purchasing_goods_received', function ($table) {
                $table->string('type_of_tax');
                $table->decimal('subtotal', 16, 4);
                $table->decimal('discount', 16, 4);
                $table->decimal('tax_base', 16, 4);
                $table->decimal('tax', 16, 4);
                $table->decimal('total', 16, 4);
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
        if (Schema::hasColumn('point_purchasing_goods_received', 'type_of_tax')) {
            Schema::table('point_purchasing_goods_received', function ($table) {
                $table->dropColumn(['type_of_tax']);
                $table->dropColumn(['subtotal']);
                $table->dropColumn(['discount']);
                $table->dropColumn(['tax_base']);
                $table->dropColumn(['tax']);
                $table->dropColumn(['total']);
            });
        }
    }
}
