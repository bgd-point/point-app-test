@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        @include('bumi-deposit::app/facility/bumi-deposit/category/_breadcrumb')
        <li>Show</li>
    </ul>

    <h2 class="sub-header">Category</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.category._menu')
    @include('core::app.error._alert')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-form">form</a></li>
                <li><a href="#block-tabs-history">history</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-form">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Category</legend>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Name</label>
                            <div class="col-md-6 content-show">
                                {{ $category->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $category->notes }}
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="tab-pane" id="block-tabs-history">
                @include('framework::app._histories')
            </div>
            <div class="tab-pane" id="block-tabs-settings">
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Action</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <a href="{{url('facility/bumi-deposit/category/'.$category->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> {{ trans('framework::framework/global.button.edit') }}</a>
                            <a onclick="secureDelete({{$category->id}},'{{ url('facility/bumi-deposit/category/delete') }}', '{{url('facility/bumi-deposit/category')}}')"
                               href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" data-toogle="tooltip"
                               title="Delete"><i class="fa fa-times"></i>
                                Delete
                            </a>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <!-- END Tabs Content -->
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
@stop
