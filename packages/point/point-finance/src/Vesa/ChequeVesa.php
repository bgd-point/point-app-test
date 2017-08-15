<?php

namespace Point\PointFinance\Vesa;

use Point\PointFinance\Models\Cheque\ChequeDetail;
use Point\PointFinance\Models\PaymentReference;

trait ChequeVesa
{
    public static function getVesa()
    {
        $array = self::vesaPending();
        $array = self::vesaReject($array);
        return $array;
    }

    public static function getVesaPending()
    {
        return self::vesaPending([], false);
    }

    public static function getVesaReject()
    {
        return self::vesaReject([], false);
    }

    private static function vesaPending($array = [], $merge_into_group = true)
    {
        $list_cheque_detail = ChequeDetail::joinCheque()->joinFormulir()->whereNull('formulir.archived')->where('point_finance_cheque_detail.status', 0)->select('point_finance_cheque_detail.*');
        // Grouping vesa
        if ($merge_into_group && $list_cheque_detail->get()->count() > 5) {
            array_push($array, [
                'url' => url('finance/point/cheque/vesa-reject'),
                'deadline' => $list_cheque_detail->orderBy('id', 'DESC')->first()->due_date,
                'message' => 'disbursement cheque from cheque pending list',
                'permission_slug' => 'create.point.finance.cashier.cheque',
                'due_date' => true
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cheque_detail->get() as $cheque_detail) {
            array_push($array, [
                'url' => url('finance/point/cheque/disbursement?id=' . $cheque_detail->id),
                'deadline' => $cheque_detail->due_date,
                'message' => 'disbursement cheque from number ' . $cheque_detail->number,
                'permission_slug' => 'create.point.finance.cashier.cheque',
                'due_date' => true
            ]);
        }

        return $array;
    }

    private static function vesaReject($array = [], $merge_into_group = true)
    {
        $list_cheque_detail = ChequeDetail::joinCheque()->joinFormulir()->whereNull('formulir.archived')->where('point_finance_cheque_detail.status', -1)->select('point_finance_cheque_detail.*');
        // Grouping vesa
        if ($merge_into_group && $list_cheque_detail->get()->count() > 5) {
            array_push($array, [
                'url' => url('finance/point/cheque/vesa-reject'),
                'deadline' => $list_cheque_detail->orderBy('id', 'DESC')->first()->due_date,
                'message' => 'Cheque have been rejected, disbursement cheque or create new from rejected list',
                'permission_slug' => 'create.point.finance.cashier.cheque',
                'due_date' => true
            ]);

            return $array;
        }

        // Push all
        foreach ($list_cheque_detail->get() as $cheque_detail) {
            array_push($array, [
                'url' => url('finance/point/cheque/reject/action/' . $cheque_detail->id),
                'deadline' => $cheque_detail->due_date,
                'message' => 'Cheque have been rejected, disbursement cheque or create new payment from cheque ' . $cheque_detail->number,
                'permission_slug' => 'create.point.finance.cashier.cheque',
                'due_date' => true
            ]);
        }

        return $array;
    }
}
