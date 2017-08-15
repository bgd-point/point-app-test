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
                <?php $i = 0;?>
                @if(!$list_cheque_detail->count())
                <h3>Please, make cheque transaction first</h3>
                @endif
                <div class="form-group row">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status" onchange="selectData()">
                            <option value="0" @if(\Input::get('status') == 0) selected @endif>pending</option>
                            <option value="1" @if(\Input::get('status') == 1) selected @endif>done</option>
                            <option value="-1" @if(\Input::get('status') == -1) selected @endif>rejected</option>
                            <option value="all" @if((\Input::get('status') == 'all') || (\Input::get('status') == null)) selected @endif>all</option>
                        </select>
                    </div>
                </div>
                @if($list_cheque_detail->count())
                <div class="table-responsive">
                    {!! $list_cheque_detail->appends(['status'=>app('request')->get('status')])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            @if(\Input::get('status') != 'all') <th width="100px" class="text-center"><input type="checkbox" id="check-all"></th> @endif
                            <th>Reference</th>
                            <th>Bank</th>
                            <th>Due Date</th>
                            <th>Number</th>
                            <th>Notes</th>
                            <th>Amount</th>
                            <th>Rejected Counter</th>
                            <th>Rejected At</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_cheque_detail as $cheque_detail)

                            <tr id="list-{{$cheque_detail->id}}">
                                @if(\Input::get('status') != 'all')
                                <td class="text-center">
                                    <input type="checkbox" name="cheque_detail_id[]" id="cheque-detail-id-{{$i}}" value="{{$cheque_detail->id}}">
                                </td>
                                @endif
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
                                <td>
                                    {{ $cheque_detail->rejected_counter ? $cheque_detail->rejected_counter .'x' : '-'}}
                                </td>
                                <td>
                                    {{ $cheque_detail->rejected_at != null ? date_format_view($cheque_detail->rejected_at) : '-' }}
                                </td>
                                <td>{!! $cheque_detail->statusLabel() !!}</td>
                            </tr>
                        <?php $i++;?>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_cheque_detail->appends(['status'=>app('request')->get('status')])->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        @if((\Input::get('status') == 0 || \Input::get('status') == -1) && \Input::get('status') != 'all' && \Input::get('status') != null)
                        <button onclick="select('disbursement')" class="btn btn-effect-ripple btn-primary">Disbursement</button>
                        @endif
                        @if((\Input::get('status') == 0 || \Input::get('status') == 1) && \Input::get('status') != 'all' && \Input::get('status') != null)
                        <button onclick="select('reject')" class="btn btn-effect-ripple btn-danger">Reject</button>
                        @endif
                    </div>
                </div>
                @endif
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

        url = '{{url()}}/finance/point/cheque/reject/?id='+cheque_detail_id;
        if (key == 'disbursement') {
            url = '{{url()}}/finance/point/cheque/disbursement/?id='+cheque_detail_id;
        }

        location.href = url;
    }

    $("#check-all").change(function () {
        $("input:checkbox").prop('checked', $(this).prop("checked"));
    });

    function selectData() {
        var status = $("#status option:selected").val();
        var url = '{{url()}}/finance/point/cheque/list?status='+status;
        location.href = url;
    }

</script>
@stop
