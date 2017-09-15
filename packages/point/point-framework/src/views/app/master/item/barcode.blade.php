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
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/item') }}">Item</a></li>
        <li>Baarcode</li>
    </ul>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')
    
    @include('core::app.error._alert')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('master/item/barcode/print') }}" method="post" class="form-horizontal">
            {!! csrf_field() !!}

                <div class="table-responsive">
                    <div class="text-center">
                        {!! $list_inventory->render() !!}
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Name</th>
                                <th>Barcode</th>
                                <th class="text-right">Number of prints</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0;?>
                            @foreach($list_inventory as $inventory)
                                <tr>
                                    <td class="text-center">
                                        <input type="hidden" name="inventory_rid[]" value="{{$inventory->id}}">
                                        @if($inventory->item->barcode) <input type="checkbox" name="inventory_id[]" value="{{$inventory->id}}">@endif
                                    </td>
                                    <td><a href="{{url('master/item/'.$inventory->item->id)}}">{{ $inventory->item->codeName }}</a></td>
                                    <td>{{ $inventory->item->barcode }}</td>
                                    <td>
                                        <input type="text" @if(!$inventory->item->barcode) disabled @endif id="number-print-{{$i}}" class="form-control format-quantity text-right" name="number_of_prints[]" value="1">
                                    </td>
                                </tr>
                            <?php $i++;?>
                            @endforeach  
                        </tbody> 
                    </table>
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary">Submit</button> 
                    </div>
                    <div class="text-center">
                        {!! $list_inventory->render() !!}
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>
@stop
