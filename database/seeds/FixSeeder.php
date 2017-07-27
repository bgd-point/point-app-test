<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\CoaGroupCategory;

class FixSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

//        \DB::statement('alter table `point_sales_payment_collection_detail` add `reference_id` int null, add `reference_type` varchar(255) null');
//        \DB::statement('alter table `point_sales_payment_collection_detail` add index `point_sales_payment_collection_detail_reference_id_index`(`reference_id`)');
//        \DB::statement('alter table `point_sales_payment_collection_detail` add index `point_sales_payment_collection_detail_reference_type_index`(`reference_type`)');
//
//        \DB::statement('alter table `point_expedition_payment_order_detail` add `reference_id` int null, add `reference_type` varchar(255) null');
//        \DB::statement('alter table `point_expedition_payment_order_detail` add index `point_expedition_payment_order_detail_reference_id_index`(`reference_id`)');
//        \DB::statement('alter table `point_expedition_payment_order_detail` add index `point_expedition_payment_order_detail_reference_type_index`(`reference_type`)');
//
//        \DB::statement('alter table `point_purchasing_payment_order_detail` add `reference_id` int null, add `reference_type` varchar(255) null');
//        \DB::statement('alter table `point_purchasing_payment_order_detail` add index `point_purchasing_payment_order_detail_reference_id_index`(`reference_id`)');
//        \DB::statement('alter table `point_purchasing_payment_order_detail` add index `point_purchasing_payment_order_detail_reference_type_index`(`reference_type`)');


        $coa_group = CoaGroupCategory::where('name', 'Current Assets & Liabilities')->first();
        $coa_group->name = 'Current Assets';
        $coa_group->save();

        \DB::commit();
    }
}
