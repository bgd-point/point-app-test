@section('scripts')

    <style>

        tbody.to-manipulate-row:after {
            content: '';
            display: block;
            height: 100px;
        }
    </style>

    <script>

        /* add costs */
        var settle_table = $('#settle-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false,
            scrollX: true
        });

        var counts;

        {{$counts}} ==
        0 ? counts = 0 : counts = {{$counts}} ;

        $('#addSettleRow').on('click', function () {
            settle_table.row.add([
                '<a href="javascript:void(0)" id="settlement-trash-' + counts + '" onClick="removeRow(' + counts + ')" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="settlement-id-' + counts + '" name="settlement_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="changeField(this.value, ' + counts + ')">'
                + '<option ></option>'
                @for($i = 0; $i<count($list_payment); $i++ )
                          + '<option value="{{$list_payment[$i][0].'_'.$list_payment[$i][1].'_'.$list_payment[$i][3].'_'.$list_payment[$i][4].'_'.$list_payment[$i][5]}}">({{$list_payment[$i][2]}}) &nbsp;&nbsp; {{ $list_payment[$i][4] }}</option>'
                @endfor
             + '</select>',
                '<input type="text" id="settle_date-' + counts + '" name="settle_date[]" class="form-control format-quantity text-right" value="" readonly/>',
                '<input type="text" id="settle_notes-' + counts + '" name="settle_notes[]" class="form-control format-quantity text-right" value="0" />',
                '<input type="text" id="settle_payment-' + counts + '" name="settle_payment[]" class="form-control format-quantity text-right" value="0" readonly/>',
                '<input type="text" id="settle_paid-' + counts + '" name="settle_paid[]" class="form-control format-quantity text-right" value="0" />',

            ]).draw(false);

            $('#settlement-id-' + counts).selectize({
                preload: true,
                sortField: [[]],
                initData: true
            });

            $('.format-quantity').autoNumeric('init', {
                vMin: '0',
                vMax: '999999999999.99',
                aPad: false,
                aSep: ',',
                aDec: '.'
            });
            $("textarea").on("click", function () {
                $(this).select();
            });
            $("input[type='text']").on("click", function () {
                $(this).select();
            });
            counts++;

        });

        $(document).on("keypress", 'form', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        $(function () {
            $('#total_detail').val(appNum(0));
        });

        function calculate() {
            var quantity = dbNum($('#quantity').val());
            var price = dbNum($('#price').val());
            var total = quantity * price;

            $('#total').val(appNum(total));

            var salvage = dbNum($('#residu').val());
            var period = dbNum($('#period').val());
            var acquisition = dbNum($('#total').val());
            var activa_cost = dbNum($('#paid').val());
            var result = (acquisition + activa_cost - salvage) / (12 * period);

            $('#depreciation').val(appNum(result));
        }

        function changeField(item_id, count) {

            var value = $("#settlement-id-" + count).val().split('_');

            var date = value[2];
            var notes = value[3];
            var payment = value[4];
            var before = dbNum($("#settle_payment-" + count).val());
            var exist = dbNum($('#total_detail').val());
            var total = (dbNum(payment) - before) + exist;

            $("#settle_date-" + count).val(date);
            $("#settle_notes-" + count).val(notes);
            $("#settle_payment-" + count).val(appNum(payment));
            $('#total_detail').val(appNum(total));
        }


        function removeRow(count) {

            var settlement_pay = dbNum($("#settle_payment-" + count).val());
            var total = dbNum($('#total_detail').val());
            var exist = total - settlement_pay;

            $('#total_detail').val(appNum(exist));

            settle_table.row($('#settlement-trash-' + count).parents('tr')).remove().draw();

        }


        $('#account_asset_id').selectize({
            onFocus: eventHandler('{{ url('purchasing/point/fixed-asset/list-asset') }}', 'account_asset_id')
        });

    </script>

@stop
