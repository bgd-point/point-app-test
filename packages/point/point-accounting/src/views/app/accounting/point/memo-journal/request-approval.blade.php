@extends('core::app.layout')

@section('scripts')
    <script>
        $("#check-all").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
        });
    </script>
@stop

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/memo-journal/_breadcrumb')
        <li><a href="{{url('accounting/point/memo-journal')}}">Memo Journal</a></li>
        <li>Request Approval</li>
    </ul>
    <h2 class="sub-header">Memo Journal | Request Approval</h2>
    @include('point-accounting::app.accounting.point.memo-journal._menu')
    
    <form action="{{url('accounting/point/memo-journal/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $list_memo_journal->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Date</th>
                                <th>Number</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Send To</th>
                                <th>Last Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list_memo_journal as $memo_journal)
                             <tr id="list-{{$memo_journal->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$memo_journal->formulir_id}}">
                                </td> 
                                <td>{{ date_format_view($memo_journal->formulir->form_date) }}</td>
                                <td><a href="{{ url('accounting/point/memo-journal/'.$memo_journal->id) }}">{{ $memo_journal->formulir->form_number}}</a></td>
                                <td>{{ number_format_accounting($memo_journal->debit) }}</td>
                                <td>{{ number_format_accounting($memo_journal->credit) }}</td>
                                <td>{{ $memo_journal->formulir->approvalTo->name }}</td>
                                <td>
                                    @if($memo_journal->formulir->request_approval_at != null)
                                        {{ date_format_view($memo_journal->formulir->request_approval_at, true) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $list_memo_journal->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Send Request</button>
                    </div>
                </div>
            </div>
        </div>          
    </form>
</div>
@stop
