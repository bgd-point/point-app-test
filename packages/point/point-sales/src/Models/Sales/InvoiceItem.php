<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;
use Point\PointSales\Models\Sales\Invoice;
use Point\PointSales\Models\Sales\InvoiceItem;

class InvoiceItem extends Model
{
    use ByTrait;

    protected $table = 'point_sales_invoice_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }

    public static function getLastPrice($item_id)
    {
        $list_item_invoice = InvoiceItem::where('item_id', $item_id)->orderBy('point_sales_invoice_id', 'desc')->get();
        if ($list_item_invoice) {
            foreach ($list_item_invoice as $item_invoice) {
                $invoice = Invoice::find($item_invoice->point_sales_invoice_id);
                if ($invoice->formulir->form_status == 1) {
                    return $item_invoice->price;
                    break;
                }
            }
        }

        return 0;
    }
}
