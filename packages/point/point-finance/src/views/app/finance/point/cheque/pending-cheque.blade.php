@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.cheque._breadcrumb')
            <li><a href="{{ url('finance/point/cheque') }}">Cheque</a></li>
            <li>Pending Cheque</li>
        </ul>
        <h2 class="sub-header">Cheque</h2>
        @include('point-finance::app.finance.point.cheque._menu')
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_cheque_detail->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Reference</th>
                            <th>Bank</th>
                            <th>Due Date</th>
                            <th>Number</th>
                            <th>Notes</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0;?>
                        @foreach($list_cheque_detail as $cheque_detail)

                            <tr id="list-{{$cheque_detail->id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="cheque_detail_id[]" id="cheque-detail-id-{{$i}}" value="{{$cheque_detail->id}}">
                                </td>
                                <td>{{ $cheque_detail->cheque->formulir->form_number }}</td>
                                <td>
                                    {{ $cheque_detail->bank}}
                                </td>
                                <td>
                                    {{ date_format_view($cheque_detail->due_date)}}
                                </td>
                                <td>
                                    {{ $cheque_detail->number}}
                                </td>
                                <td>
                                    {{ $cheque_detail->notes}}
                                </td>
                                <td>
                                    {{ number_format_price($cheque_detail->amount)}}
                                </td>
                            </tr>
                        <?php $i++;?>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_cheque_detail->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button onclick="select('liquid')" class="btn btn-effect-ripple btn-primary">Liquid</button>
                        <button onclick="select('decline')" class="btn btn-effect-ripple btn-danger">Decline</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script type="text/javascript">

    function select(key) {
        var cheque_detail_id = [];
        for (var i = 0; i < {{$i}}; i++) {
            if ($("#cheque-detail-id-"+i).is(":checked")) {    
                cheque_detail_id.push($("#cheque-detail-id-"+i).val());
            }
        };

        url = '{{url()}}/finance/point/cheque/decline/?id='+cheque_detail_id;
        if (key == 'liquid') {
            url = '{{url()}}/finance/point/cheque/liquid/?id='+cheque_detail_id;
        }

        location.href = url;
    }
</script>
@stop
