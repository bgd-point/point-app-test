<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;

class FixE1 extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();

        $goods_receiveds = \Point\PointPurchasing\Models\Inventory\GoodsReceived::all();
        foreach($goods_receiveds as $goods_received) {
            $locks = FormulirLock::where('locking_id', $goods_received->formulir_id)->get();
            $efee = 0;
            foreach ($locks as $lock) {
                \Log::info('model : '.$lock->lockedForm->formulirable_type);
                if ('Point\PointExpedition\Models\ExpeditionOrder' == $lock->lockedForm->formulirable_type) {
                    $model = $lock->lockedForm->formulirable_type;
                    $eo = $model::find($lock->lockedForm->formulirable_id);

                    $lock2 = FormulirLock::where('locking_id', $eo->formulir_id)->first();
                    if($lock2) {
                        $locks3 = FormulirLock::where('locked_id', $lock2->locked_id)->get();
                        foreach($locks3 as $lock3) {
                            if(!FormulirLock::where('locked_id', $lock3->locking_id)->where('locking_id', $goods_received->formulir_id)->first()) {
                                \Point\Framework\Helpers\FormulirHelper::lock($lock3->locking_id, $goods_received->formulir_id);
                            }
                        }
                    }

                    $efee += $eo->expedition_fee;
                    \Log::info($efee);
                }
            }

            if ($efee > 0) {
                $goods_received->expedition_fee = $efee;
                $goods_received->save();
            }
        }
        \DB::commit();
    }
}
