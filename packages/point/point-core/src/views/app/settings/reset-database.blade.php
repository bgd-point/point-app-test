@extends('core::app.layout')

@section('content')
    <div id="page-content" class="inner-sidebar-left">

        @include('core::app.settings._sidebar')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="sub-header">Reset Database</h2>
                        <form action="reset-database/to-default" method="post" class="form-horizontal form-bordered">
                            {!! csrf_field() !!}
                            <fieldset>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <input type="submit" value="reset database" class="btn btn-danger">
                                        <span class="help-block">This action will remove all your database, and generate default data for testing purpose</span>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
