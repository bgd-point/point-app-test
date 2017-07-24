@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.output._breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Output</h2>
        @include('point-manufacture::app.manufacture.point.output._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <div class="form-horizontal form-bordered">
                @if($output_archived->canceled_at != null)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissable">
                                <h1 class="text-center"><strong>Canceled</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                @if($output_archived->archived != null)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissable">
                                <h1 class="text-center"><strong>Archived</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                @if($output_archived->approval_status == 1)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-success alert-dismissable">
                                <h1 class="text-center"><strong>Approved</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                @if($output_archived->approval_status == -1)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissable">
                                <h1 class="text-center"><strong>Rejected</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form number</label>

                    <div class="col-md-6 content-show">
                        {{ $output_archived->nomer ? $output_archived->nomer : $output_archived->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form date</label>

                    <div class="col-md-6 content-show">
                        {{ \DateHelper::viewFormat($output_archived->tanggal, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">warehouse</label>

                    <div class="col-md-6 content-show">
                        {{ $output_archived->gudang->codeName }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Driver name</label>

                    <div class="col-md-6 content-show">
                        {{ $output_archived->nama_sopir }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">car number</label>

                    <div class="col-md-6 content-show">
                        {{ $output_archived->nopol }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">notes</label>

                    <div class="col-md-6 content-show">
                        {{ $output_archived->notes }}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> raw material</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>item</th>
                                        <th class="text-right">quantity</th>
                                        <th>warehouse</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($output_archived->items as $output_item)
                                        <tr>
                                            <td></td>
                                            <td>{{ $output_item->item->codeName }}</td>
                                            <td class="text-right">{{ \NumberHelper::formatToApp($output_item->jumlah) }}</td>
                                            <td>{{ $output_item->satuan }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> person in charge</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form creator</label>

                        <div class="col-md-6 content-show">
                            {{ $output_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Status</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @if($output_archived->approval_status == 0)
                                <label class="label label-warning">Pending</label>
                            @elseif($output_archived->approval_status == 1)
                                <label class="label label-success">Done</label>
                            @elseif($output_archived->approval_status == -1)
                                <label class="label label-danger">Canceled</label>
                            @endif
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
