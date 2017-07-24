@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/item') }}">Item</a></li>
        <li>Journal</li>
    </ul>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Description</th>
                    <th>Debit Account</th>
                    <th>Credit Account</th>
                </tr>
                </thead>
                <tbody>
                    <form action="{{ url('master/item/journal/update-opening-balance') }}" method="post" class="form-horizontal">
                        {!! csrf_field() !!}

                    <tr>
                       <td colspan="3"><h4>OPENING BALANCE INVENTORY</h4></td>
                    </tr>
                    <tr>
                        <td>Inventory Account</td>
                        <td>
                            <input type="text" class="form-control" value="AUTOMATIC" readonly>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Retained Earning Account</td>
                        <td></td>
                        <td>
                            <select name="retained_earning_account" class="selectize">
                                @foreach($list_coa as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->account }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary btn-block">Save</button></td>
                    </tr>
                    </form>
                </tbody>
            </table>
        </div>
    </div>  
</div>
@stop
