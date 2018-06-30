<?php

namespace Point\PointFinance\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\FormulirLock;
use Point\PointFinance\Helpers\PaymentHelper;
use Point\PointFinance\Models\PaymentReference;
use Point\PointPurchasing\Models\Service\Downpayment;

class PaymentController extends Controller
{
    public function choose($payment_reference_id)
    {
        $payment_reference = PaymentReference::where('payment_reference_id', $payment_reference_id)->first();
        return view('point-finance::app.finance.point.choose_payment')->with(['payment_reference' => $payment_reference]);
    }

    public function cancel()
    {
        $permission_slug = app('request')->input('permission_slug');
        $formulir_id = app('request')->input('formulir_id');

        $locks = FormulirLock::where('locking_id', $formulir_id)->where('locked', 1)->get();
        foreach ($locks as $lock) {
            if (strpos($lock->lockedForm->formulirable_type, 'Downpayment') !== false) {
                $locks2 = FormulirLock::where('locked_id', $lock->locked_id)->where('locked', 1)->get();
                if ($locks2->count() > 1) {
                    if ($locks2->first()->lockedForm->formulirable_type != $lock->lockedForm->formulirable_type) {
                        gritter_error('Cannot delete this transaction');
                        return array('status' => 'success');
                    }
                }
            }
        }
        DB::beginTransaction();

        FormulirHelper::cancel($permission_slug, $formulir_id);
        PaymentHelper::cancelPayment($formulir_id);

        DB::commit();

        return array('status' => 'success');
    }
}
