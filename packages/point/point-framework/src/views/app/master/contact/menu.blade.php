@extends('core::app.layout')

@section('content')
<div id="page-content" class="inner-sidebar-left">

    @include('framework::app.master.contact._sidebar')
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Contact</li>
    </ul>
</div>
@stop
