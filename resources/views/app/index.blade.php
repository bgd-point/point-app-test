@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <h2 class="sub-header"><i class="fa fa-user"></i> Vesa <br/><span style="font-size:14px" class="label label-primary label-xs">Virtual Enterprise Smart Assistance</span></h2>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-vcenter">
                        <tr bgcolor="black" style="color:#FFF">
                            <td colspan='4'><strong>DUE DATE LIST </strong></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td style="widtd:150px"><b>Deadline</b></td>
                            <td style="widtd:150px"><b>Feature</b></td>
                            <td><b>Description</b></td>
                        </tr>
                        <?php
                        $title = '';

                        // VESA ORDER BY DEADLINE
                        $array_vesa = array_values(array_sort($array_vesa, function ($value) {
                            return $value['deadline'];
                        }));

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

                        @foreach($due_date as $vesa)
                            @if(auth()->user()->may($vesa['permission_slug']))
                                <?php
                                $permission = Point\Core\Models\Master\Permission::where('slug', $vesa['permission_slug'])->first();
                                $permission = str_replace('POINT', '', $permission->group);
                                ?>
                                <tr bgcolor="#e25757" style="color:#FFF">
                                    <td style="vertical-align: top"><a href="{{ $vesa['url'] }}"><i class="fa fa-share-square-o" style="color:#fff"></i></a></td>
                                    <td style="vertical-align: top">{{ date_format_view($vesa['deadline']) }}</td>
                                    <td><b>{{ $permission }}</b></td>
                                    <td>{{ $vesa['message'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr> <td colspan="4"></td></tr>
                        <tr bgcolor="black" style="color:#FFF">
                            <td colspan="4" ><strong>TO DO LIST</strong></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="widtd:150px"><b>Deadline</b></td>
                            <td style="widtd:150px"><b>Feature</b></td>
                            <td><b>Description</b></td>
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
                                    <td>{!! $vesa['message'] !!}</td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
