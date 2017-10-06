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
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/invoice') }}">Invoice</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Request Approval Print Invoice</h2>
        @include('point-sales::app.sales.point.sales.invoice._menu')

        <form action="{{url('sales/point/indirect/invoice/send-request-approval')}}" method="post">
            {!! csrf_field() !!}
            <input type="hidden" name="id" value="{{$invoice->id}}">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="form-horizontal form-bordered">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Reference</label>
                            <div class="col-md-6 content-show">
                                {{ $invoice->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($invoice->formulir->form_date) }}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval Print To</label>
                            <div class="col-md-4">
                                <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.sales.downpayment'))
                                            <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-effect-ripple btn-primary">Send Request</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
