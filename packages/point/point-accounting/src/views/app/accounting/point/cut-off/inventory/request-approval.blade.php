@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/inventory/_breadcrumb')
        <li>Request approval</li>
    </ul>
    <h2 class="sub-header">Cut Off Account Inventory</h2>
     @include('point-accounting::app.accounting.point.cut-off.inventory._menu')
    
    <form action="{{url('accounting/point/cut-off/inventory/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $list_cut_off->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list_cut_off as $cut_off)
                            <tr id="list-{{$cut_off->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$cut_off->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($cut_off->formulir->form_date) }}</td>
                                <td><a href="{{ url('accounting/point/cut-off/inventory/'.$cut_off->id) }}">{{ $cut_off->formulir->form_number}}</a></td>
                                <td>{{ $cut_off->formulir->approvalTo->name }}</td>
                                <td>
                                    @if($cut_off->formulir->request_approval_at != '0000-00-00 00:00:00' and $cut_off->formulir->request_approval_at != null)
                                        {{ date_format_view($cut_off->formulir->request_approval_at, true) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $list_cut_off->render() !!}
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
