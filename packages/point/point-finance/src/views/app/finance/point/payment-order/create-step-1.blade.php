@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.payment-order._breadcrumb')
         <li>Create step 1</li>
    </ul>
    <h2 class="sub-header">Payment Order</h2>
    @include('point-finance::app.finance.point.payment-order._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{url('finance/point/payment-order/create-step-2')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 
                </fieldset>                
                 
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment To *</label>
                    <div class="col-md-6">
                        <div class="@if(access_is_allowed_to_view('create.customer') || access_is_allowed_to_view('create.supplier') || access_is_allowed_to_view('create.expedition')) input-group @endif">
                            <select id="person_id" name="person_id" class="selectize" style="width: 100%;" data-placeholder="Please choose">
                                <option></option>
                                @foreach($list_person as $person)
                                <option value="{{$person->id}}" @if(old('person') == $person->id) selected @endif>{{$person->codeName}}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <a href="#modal-contact" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </span>
                        </div>
                    </div>
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

@include('framework::app.master.contact.__create-person')
@stop
