@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/contract') }}">Contract</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Join Contract</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.contract._menu')
        
        @include('core::app.error._alert')
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/fixed-assets/contract/join/store')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input type="hidden" name="contract_reference_id" value="{{$contract_reference_id}}">
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 
                </fieldset>                
                 
                <div class="form-group">
                    <label class="col-md-3 control-label">Join To Contract *</label>
                    <div class="col-md-6">
                        <select id="contract_id" name="contract_id" class="selectize" style="width: 100%;" data-placeholder="Please choose one ..." onchange="selectContract(this.value)">
                            <option></option>
                            @foreach($list_contract as $contract)
                            <option value="{{$contract->formulirable_id}}" @if(old('contract_id') == $contract->id) selected @endif>{{$contract->formulir->form_number}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>    
                <div class="form-group result">
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Next</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>
@stop
@section('scripts')

<script type="text/javascript">
    function selectContract(contract_id) {
        $(".result").fadeOut();
        $.ajax({
            url: '{{url("purchasing/point/fixed-assets/contract/join/get-detail-contract")}}',
            data: {contract_id: contract_id},
            success: function(result) {
                $(".result").html(result);
                $(".result").fadeIn();
            }
        })
    }
</script>

@stop

