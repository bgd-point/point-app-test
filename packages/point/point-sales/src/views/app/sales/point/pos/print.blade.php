
<!DOCTYPE html>
<html>
<head>
    <title>PRINT POS</title>
    <style>
        @media print {
            * {
                text-transform: uppercase;
                width: 230px;
                font-family: "Lucida Console", Monaco, monospace;
                font-size: {{ \Point\Core\Models\Setting::getFontSize() }}px;
            }
            table {
                
                border-collapse: collapse;
                border-spacing: 0;
            }
            tr, th, td {
                border: 0;
                border-collapse: collapse;
                border-spacing: 0;
                padding: 3px 5px;
            } 
            .text-left {
                text-align: left;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }
        }    

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body onLoad="window.print()">
    <div class="page" 
    @if($warehouse_profiles->store_name)
    <strong style="font-size:18px; text-transform: uppercase;">{{$warehouse_profiles->store_name}}</strong> <br/>
    <font style="font-size:12px;text-transform: capitalize;">
        {{$warehouse_profiles->address}} <br/>
        {{$warehouse_profiles->phone}} 
    </font>
    @else
    Store Name <br/>
    Addess......... <br/>
    Phone Number 
    @endif
    <br/> <hr><br/>

    @if($pos_sales->canceled_at != null)
    <div class="col-md-12 text-center" style="color:red">
        Cancelled <br/>
        {{ date_format_view($pos_sales->canceled_at,'true') }}
    </div>
    @endif
    <br>
    <div class="col-md-12 text-left">
        {{date_format_view($pos_sales->formulir->form_date,true)}}
        <br>
        {{$pos_sales->formulir->form_number}}
    </div>
    <br/> <br/>
    
    <table>
        <thead>
            <tr>
                <th class="text-left" style="margin-right:20px">Item</th>
                <th class="text-right">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pos_sales->items as $pos_item)
            <tr>
                <td>{{ $pos_item->item->name }}</td>
                <td></td>
            </tr>
            <tr>
                <td>Qty: {{ number_format_quantity($pos_item->quantity, 0) }}</td>
                <td class="text-right">{{ number_format_accounting($pos_item->quantity * ($pos_item->price - $pos_item->discount), 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2"><hr></td>
            </tr>
            <tr>
                <td class="text-right">Sub Total</td>
                <td class="text-right">{{number_format_accounting($pos_sales->subtotal, 2)}}</td>
            </tr>
            @if($pos_sales->discount > 0)
            <tr>
                <td class="text-right">Discount</td>
                <td class="text-right">{{number_format_accounting($pos_sales->discount, 2)}}</td>
            </tr>
            @endif
            @if($pos_sales->tax > 0)
            <tr>
                <td class="text-right">Tax</td>
                <td class="text-right">{{number_format_accounting($pos_sales->tax, 2)}}</td>
            </tr>
            @endif
            <tr>
                <td class="text-right">Total</td>
                <td class="text-right">{{number_format_accounting($pos_sales->total, 2)}}</td>
            </tr>
            <tr>
                <td class="text-right">Money Received</td>
                <td class="text-right">{{number_format_accounting($pos_sales->money_received, 2)}}</td>
            </tr>
            <tr>
                <td class="text-right">Change</td>
                <td class="text-right">{{number_format_accounting($pos_sales->money_received - $pos_sales->total, 2)}}</td>
            </tr>
            <tr><td></td><td></td></tr>
            <tr><td></td><td></td></tr>
            <tr><td></td><td></td></tr>
            <tr><td></td><td></td></tr>
            <tr style="font-size:12px;">
             <td colspan="2" class="text-center">{{ get_end_notes('sales pos') }}</td> 
         </tr>

     </tbody>
 </table>
 <br />
 <br />
</div>

</body>
</html>
