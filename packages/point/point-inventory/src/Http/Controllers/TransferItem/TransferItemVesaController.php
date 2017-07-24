<?php

namespace Point\PointInventory\Http\Controllers\TransferItem;

use App\Http\Controllers\Controller;
use Point\PointInventory\Models\TransferItem\TransferItem;

class TransferItemVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.inventory.transfer.item');

        $view = view('app.index');
        $view->array_vesa = TransferItem::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.inventory.transfer.item');

        $view = view('app.index');
        $view->array_vesa = TransferItem::getVesaReject();
        return $view;
    }

    public function receive()
    {
        access_is_allowed('update.point.inventory.transfer.item');

        $view = view('app.index');
        $view->array_vesa = TransferItem::getVesaReceive();
        return $view;
    }
}
