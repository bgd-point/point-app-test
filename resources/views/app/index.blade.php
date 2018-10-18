@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <h2 class="sub-header"><i class="fa fa-user"></i> Vesa <br/><span style="font-size:14px" class="label label-primary label-xs">Virtual Enterprise Smart Assistance</span></h2>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-vcenter">

                        <?php
                            $title = '';

                            // VESA ORDER BY DEADLINE
                            $array_vesa = array_values(array_sort($array_vesa, function ($value) {
                                return $value['deadline'];
                            }));
                            if(isset($array_vesa_payment)){
                                $array_vesa_payment = array_values(array_sort($array_vesa_payment, function ($value) {
                                    return $value['deadline'];
                                }));
                            }


                            // VESA DUE DATE
                            $due_date = array_where($array_vesa, function ($value, $key) use ($array_vesa) {
                                if (array_key_exists('due_date', $key)) {
                                    return $key['due_date'] == true;
                                }
                            });

                            // VESA TODO
                            $todo = array_where($array_vesa, function ($value, $key) use ($array_vesa) {
                                if (! array_key_exists('due_date', $key)) {
                                    return $key['deadline'] != '';
                                } else {
                                    return $key['due_date'] == false;
                                }
                            });
                        ?>

                        @if(isset($array_vesa_payment))
                        <tr bgcolor="black" style="color:#FFF">
                            <td colspan='5'><strong>Approved Formulir, please create payment </strong></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><b>Deadline</b></td>
                            <td><b>Form Number</b></td>
                            <td><b>Supplier</b></td>
                            <td class="text-right"><b>Amount</b></td>
                        </tr>
                        @foreach($array_vesa_payment as $vesa)
                            @if(auth()->user()->may($vesa['permission_slug']))
                                <?php
                                $permission = Point\Core\Models\Master\Permission::where('slug', $vesa['permission_slug'])->first();
                                $permission = str_replace('POINT', '', $permission->group);
                                ?>
                                <tr>
                                    <td style="vertical-align: top"><a href="{{ $vesa['url'] }}"><i class="fa fa-share-square-o"></i></a></td>
                                    <td style="vertical-align: top; white-space: nowrap;">{{ date_format_view($vesa['deadline']) }}</td>
                                    <td style="vertical-align: top; white-space: nowrap;"> {!! formulir_url($vesa['data']->reference) !!}</td>
                                    <td>{{ $vesa['data']->person->name }}</td>
                                    {{-- <td colspan="2">{!! $vesa['message'] !!}</td> --}}
                                    <td class="text-right">{{ number_format_price($vesa['data']->total) }}</td>
                                </tr>
                            @endif
                        @endforeach
                        @endif

                        <tr> <td colspan="5"></td></tr>
                        <tr bgcolor="black" style="color:#FFF">
                            <td colspan='5'><strong>DUE DATE LIST </strong></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="widtd:150px"><b>Deadline</b></td>
                            <td style="widtd:150px"><b>Feature</b></td>
                            <td colspan="2"><b>Description</b></td>
                        </tr>

                        @foreach($due_date as $vesa)
                            @if(auth()->user()->may($vesa['permission_slug']))
                                <?php
                                $permission = Point\Core\Models\Master\Permission::where('slug', $vesa['permission_slug'])->first();
                                $permission = str_replace('POINT', '', $permission->group);
                                ?>
                                <tr>
                                    <td style="vertical-align: top"><a href="{{ $vesa['url'] }}"><i class="fa fa-share-square-o"></i></a></td>
                                    <td style="vertical-align: top">{{ date_format_view($vesa['deadline']) }}</td>
                                    <td><b>{{ $permission }}</b></td>
                                    <td colspan="2"><button class="btn btn-xs btn-danger"><i class="fa fa-warning"></i> DUE</button> {!! $vesa['message'] !!}</td>
                                </tr>
                            @endif
                        @endforeach

                        <tr> <td colspan="5"></td></tr>
                        <tr bgcolor="black" style="color:#FFF">
                            <td colspan="5" ><strong>TO DO LIST</strong></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="widtd:150px"><b>Deadline</b></td>
                            <td style="widtd:150px"><b>Feature</b></td>
                            <td colspan="2"><b>Description</b></td>
                        </tr>
                        @foreach($todo as $vesa)
                            @if(auth()->user()->may($vesa['permission_slug']))
                                <?php
                                $permission = Point\Core\Models\Master\Permission::where('slug', $vesa['permission_slug'])->first();
                                $permission = str_replace('POINT', '', $permission->group);
                                ?>
                                <tr>
                                    <td style="vertical-align: top"><a href="{{ $vesa['url'] }}"><i class="fa fa-share-square-o"></i></a></td>
                                    <td style="vertical-align: top">{{ date_format_view($vesa['deadline']) }}</td>
                                    <td><b>{{ $permission }}</b></td>
                                    <td colspan="2">{!! $vesa['message'] !!}</td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btn-sync-budget"
                    onclick="btnSyncBudgetClicked(this);">
                    <i class="fa fa-spinner fa-spin hidden"></i>
                    Sync Budget
                </button>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
function btnSyncBudgetClicked(event) {
    $(event).prop('disabled', true);
    $(event).find('i').toggleClass('hidden');

    sendSyncBudget()
}
function sendSyncBudget() {
    $.get('/sync-budget', function(data) {
        $('#btn-sync-budget').prop('disabled', false)
        $('#btn-sync-budget').find('i').toggleClass('hidden')
    })
}
</script>
@stop