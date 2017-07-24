@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/goods-received') }}">Goods Received</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Goods Received</h2>
        @include('point-purchasing::app.purchasing.point.inventory.goods-received._menu')

        <div class="panel panel-default">
            <div class="panel-body">
            @if(! $list_person)
            <h3>Make purchasing order first</h3>
            @endif
            @if($list_person)
            Select Supplier <br>
            <select class="selectize" id="supplier-id" data-placeholder="Choose one..">
                <option></option>
                @foreach($list_person as $person)
                <option value="{{$person->id}}">{{$person->name}}</option>
                @endforeach
            </select>

            <button class="btn btn-primary btn-effect-ripple" onclick="next()">Next</button>
            @endif
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script type="text/javascript">
    function next (argument) {
        if ($("#supplier-id").val() == "") {
            swal('Please, select supplier');
            return false;
        }

        var url = '{{url()}}/purchasing/point/goods-received/create-step-2/'+$("#supplier-id").val();
        location.href = url;
    }
</script>
@stop
