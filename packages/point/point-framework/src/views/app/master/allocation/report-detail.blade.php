@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Allocation</li>
    </ul>

    <h2 class="sub-header">Allocation</h2>
    @include('framework::app.master.allocation._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="table-responsive">
            {!! $list_allocation_report->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Form Date</th>
                            <th>Form Number</th>
                            <th>Allocation</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $amount = 0;?>
                        @foreach($list_allocation_report as $allocation_report)
                        <?php 
                            $amount += $allocation_report->amount
                        ?>
                        <tr id="list-{{$allocation_report->id}}">
                            <td>{{date_format_view($allocation_report->formulir->form_date)}}</td>
                            <td>{{$allocation_report->formulir->form_number}}</td>
                            <td>{{ $allocation_report->allocation->name }}</td>
                            <td class="text-right">{{number_format_quantity($allocation_report->amount)}}</td>
                        </tr>
                        @endforeach 
                        <tr>
                            <td colspan="3" class="text-right h4"><strong>Total</strong></td>
                            <td class="text-right h4"><strong>{{number_format_quantity($amount)}}</strong></td>
                        </tr> 
                    </tbody> 
                </table>
               {!! $list_allocation_report->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
