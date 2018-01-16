<?php

use Illuminate\Database\Seeder;

class FixSalesSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();
        $delivery_orders = \Point\PointSales\Models\Sales\DeliveryOrder::joinFormulir()->notArchived()->get();
        foreach ($delivery_orders as $delivery_order) {
            $locks = \Point\Framework\Models\FormulirLock::where('locked_id', $delivery_order->formulir_id)->where('locked', true)->get();
            if($locks->count()) {
                $delivery_order->formulir->form_status = 1;
                $delivery_order->formulir->save();

                foreach ($locks as $lock) {
                    \Log::info("D: " .$delivery_order->formulir->form_number . " = ". $lock->locking->form_number);
                }
            }
        }

        \DB::commit();
    }
}
