@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        @include('bumi-deposit::app/facility/bumi-deposit/category/_breadcrumb')
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Category</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.category._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{ url('facility/bumi-deposit/category') }}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 

                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-6">
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes') }}" />
                        </div>
                    </div>
                </fieldset>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">{{ trans('framework::framework/global.button.submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<style>
    tbody.manipulate-row:after {
      content: '';
      display: block;
      height: 100px;
    }
</style>
<script>
var item_table = $('#item-datatable').DataTable({
        bSort: false,
        bPaginate: false,
        bInfo: false,
        bFilter: false,
        bScrollCollapse: false,
        scrollX: true
    });
</script>
@stop
