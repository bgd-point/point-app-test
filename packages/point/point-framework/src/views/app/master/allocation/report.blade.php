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
            <form action="{{ url('master/allocation/report') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-4">
                        <div class="input-group input-daterange" data-date-format="{{ date_format_masking()}}">
                            <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"  placeholder="Date from" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="Date to" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="search" id="search" placeholder="search allocation .. " value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                        <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" onclick="exportExcel()"> Export to excel</a>
                        <div id="preloader" style="display:none; margin-top:5px; float: left;position: relative;margin-top: -29px;margin-left: 250px;">
                            <i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>
                        </div>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
            {!! $list_allocation_report->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
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
                            <td><a href="{{ url('master/allocation/report/detail/'.$allocation_report->allocation->id.'/?date_from='.\Input::get('date_from').'&date_to='. \Input::get('date_to')) }}">{{ $allocation_report->allocation->name }}</a></td>
                            <td class="text-right">{{number_format_quantity($allocation_report->amount)}}</td>
                        </tr>
                        @endforeach 
                        <tr>
                            <td class="text-right h4"><strong>Total</strong></td>
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

@section('scripts')
<script type="text/javascript">
    function exportExcel() {
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var search = $("#search").val();
        $("#preloader").fadeIn();
        $(".button-export").addClass('disabled');
        $.ajax({
            url: '{{url("master/allocation/export/")}}',
            data: {
                date_from: date_from,
                date_to: date_to,
                search: search
            },
            success: function (data) {
                console.log(data);
                if (data.status == 'success') {
                    $("#preloader").fadeOut();
                    $(".button-export").removeClass('disabled');
                    notification('exporting master item, we will send you an email when it finished');
                }
            }, error:  function (data) {
                console.log(data);
                $("#preloader").fadeOut();
                $(".button-export").removeClass('disabled');
                notification('export item data failed, please try again');
            }

        });
    }
</script>
@stop
