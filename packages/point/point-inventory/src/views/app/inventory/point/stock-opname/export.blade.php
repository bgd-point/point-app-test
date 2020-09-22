<tr>
    <td>STOCK OPNAME</td>
</tr>

<tr>
    <td>#</td>
    <td>STATUS</td>
    <td>DATE</td>
    <td>WAREHOUSE</td>
    <td>ITEM CODE</td>
    <td>ITEM</td>
    <td>FROM (QTY)</td>
    <td>TO (QTY)</td>
    <td>PRICE</td>
    <td>DIFFERENCE (QTY)</td>
    <td>DIFFERENCE (VALUE)</td>
</tr>

<?php
    $items = \Point\Framework\Models\Master\Item::all();
    foreach ($items as $item) {
        $lastBuy = \Point\PointPurchasing\Models\Inventory\InvoiceItem::join('point_purchasing_invoice', 'point_purchasing_invoice.id', '=', 'point_purchasing_invoice_item.point_purchasing_invoice_id')
            ->join('formulir', 'point_purchasing_invoice.formulir_id', '=', 'formulir.id')
            ->where('point_purchasing_invoice_item.item_id', '=', $item->id)
            ->where('formulir.form_date', '<=', \Carbon\Carbon::now())
            ->orderBy('formulir.form_date', 'desc')
            ->first();

        $price = 0;

        if ($lastBuy) {
            $price = $lastBuy->price;
        } else {
            $ci = \Point\PointAccounting\Models\CutOffInventoryDetail::where('subledger_id', $item->id)->first();
            if ($ci) {
                $price = $ci->amount / $ci->stock;
            } else {
                $product = \Point\PointManufacture\Models\InputProduct::join('point_manufacture_input', 'point_manufacture_input.id', '=', 'point_manufacture_input_product.input_id')
                    ->join('formulir', 'point_manufacture_input.formulir_id', '=', 'formulir.id')
                    ->where('formulir.form_date', '<=', \Carbon\Carbon::now())
                    ->whereNotNull('formulir.form_number')
                    ->where('formulir.form_status', '!=', -1)
                    ->where('product_id', $item->id)
                    ->first();

                if ($product) {
                    $outputProduct = \Point\PointManufacture\Models\OutputProduct::join('point_manufacture_output', 'point_manufacture_output.id', '=', 'point_manufacture_output_product.output_id')
                        ->where('point_manufacture_output.input_id', $product->input_id)
                        ->first();
                    $materials = \Point\PointManufacture\Models\InputMaterial::where('input_id', $product->input_id)->get();
                    $price = 0;
                    foreach ($materials as $material) {
                        $lastBuyMaterial = \Point\PointPurchasing\Models\Inventory\InvoiceItem::join('point_purchasing_invoice', 'point_purchasing_invoice.id', '=', 'point_purchasing_invoice_item.point_purchasing_invoice_id')
                            ->join('formulir', 'point_purchasing_invoice.formulir_id', '=', 'formulir.id')
                            ->where('point_purchasing_invoice_item.item_id', '=', $material->material_id)
                            ->where('formulir.form_date', '<=', \Carbon\Carbon::now())
                            ->whereNotNull('formulir.form_number')
                            ->orderBy('formulir.form_date', 'desc')
                            ->first();

                        if ($lastBuyMaterial && $outputProduct) {
                            $price += ($material->quantity * $lastBuyMaterial->price) / $outputProduct->quantity;
                        }
                    }
                } else {
                    $oi = \Point\Framework\Models\OpeningInventory::where('item_id', '=', $item->id)->first();
                    if ($oi) {
                        $price = $oi->price;
                    }
                }
            }
        }
        $item->price = $price;
    }
?>

@foreach($list_report as $opname)
    @foreach ($opname->items as $detail)
    <?php
    $price = 0;
    foreach ($items as $item) {
        if ($item->id == $detail->item_id) {
            $price = $item->price;
        }
    }
    ?>

    <tr>
        <td>{{ $opname->formulir->form_number }}</td>
        <td>
            @if($opname->formulir->form_status == 0)
                OPEN
            @elseif($opname->formulir->form_status == 1)
                CLOSED
            @elseif($opname->formulir->form_status == -1)
                CANCEL
            @endif
        </td>
        <td>{{ $opname->formulir->form_date }}</td>
        <td>{{ $opname->warehouse->name }}</td>
        <td>{{ $detail->item->code }}</td>
        <td>{{ $detail->item->name }}</td>
        <td>{{ $detail->stock_in_database }}</td>
        <td>{{ $detail->quantity_opname }}</td>
        <td>{{ $price }}</td>
        <td>{{ $detail->quantity_opname - $detail->stock_in_database }}</td>
        <td>{{ $price * ($detail->quantity_opname - $detail->stock_in_database) }}</td>
    </tr>
    @endforeach
@endforeach
