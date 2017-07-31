@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Bank</li>
    </ul>

    <h2 class="sub-header">Bank</h2>
    @include('framework::app.master.bank._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('master/bank') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status">
                            <option value="0" @if(\Input::get('status')== 0) selected @endif>Enable</option>                            
                            <option value="1" @if(\Input::get('status')== 1) selected @endif>Disabled</option>                            
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search Name..." value="{{\Input::get('search')}}" autofocus>
                    </div>
                    <div class="col-sm-6">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button> 
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
            
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>                                
                            <th>CODE</th>
                            <th>NAME</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_bank as $bank)
                        <tr id="list-{{$bank->id}}">
                            <td class="text-center">
                                <a href="{{ url('master/bank/'.$bank->id) }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i></a>

                                <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" class="btn btn-effect-ripple btn-xs btn-danger" onclick="secureDelete({{$bank->id}}, '{{url('master/bank/delete')}}')"><i class="fa fa-times"></i></a>

                                <a id="link-state-{{$bank->id}}" href="javascript:void(0)" data-toggle="tooltip" 
                                title="{{$bank->disabled == 0 ? 'disable' : 'enable' }}" 
                                class="btn btn-effect-ripple btn-xs {{$bank->disabled == 0 ? 'btn-success' : 'btn-default' }}" 
                                onclick="state({{$bank->id}})">
                                <i id="icon-state-{{$bank->id}}" class="{{$bank->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i></a>

                            </td> 
                            <td><a href="{{ url('master/bank/'.$bank->id) }}">{{ $bank->code }}</a></td>
                            <td><a href="{{ url('master/bank/'.$bank->id) }}">{{ $bank->name }}</a></td>
                        </tr>
                        @endforeach  
                    </tbody> 
                </table>
                 
            </div>
        </div>
    </div>  
</div>
@stop
