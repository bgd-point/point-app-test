@extends('core::app.layout')
 
@section('content')
<div id="page-content" class="inner-sidebar-left">
	
	@include('core::app.settings._sidebar')

	<div class="panel panel-default"> 
        <div class="panel-body"> 

            <h4 class="sub-header">Default</h4>
            <ul class="sidebar-themes clearfix"> 
                <li>
                    <a href="javascript:void(0)" class="themed-background-classy" data-toggle="tooltip" title="Classy" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/classy.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="">
                        <span class="section-side themed-background-dark-classy"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-social" data-toggle="tooltip" title="Social" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/social.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="">
                        <span class="section-side themed-background-dark-social"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-flat" data-toggle="tooltip" title="Flat" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/flat.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="">
                        <span class="section-side themed-background-dark-flat"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-amethyst" data-toggle="tooltip" title="Amethyst" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/amethyst.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="">
                        <span class="section-side themed-background-dark-amethyst"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-creme" data-toggle="tooltip" title="Creme" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/creme.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="">
                        <span class="section-side themed-background-dark-creme"></span>
                        <span class="section-content"></span>
                    </a>
                </li> 
                <li>
                    <a href="javascript:void(0)" class="themed-background-passion" data-toggle="tooltip" title="Passion" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/passion.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="">
                        <span class="section-side themed-background-dark-passion"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
            </ul> 

            <h4 class="sub-header">Light Sidebar</h4>
            <ul class="sidebar-themes clearfix">  
                <li>
                    <a href="javascript:void(0)" class="themed-background-classy" data-toggle="tooltip" title="Classy + Light Sidebar" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/classy.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light">
                        <span class="section-side"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-social" data-toggle="tooltip" title="Social + Light Sidebar" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/social.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light">
                        <span class="section-side"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-flat" data-toggle="tooltip" title="Flat + Light Sidebar" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/flat.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light">
                        <span class="section-side"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-amethyst" data-toggle="tooltip" title="Amethyst + Light Sidebar" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/amethyst.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light">
                        <span class="section-side"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-creme" data-toggle="tooltip" title="Creme + Light Sidebar" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/creme.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light">
                        <span class="section-side"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-passion" data-toggle="tooltip" title="Passion + Light Sidebar" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/passion.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light">
                        <span class="section-side"></span>
                        <span class="section-content"></span>
                    </a>
                </li> 
            </ul>

            <h4 class="sub-header">Light Header</h4>
            <ul class="sidebar-themes clearfix">
                <li>
                    <a href="javascript:void(0)" class="themed-background-classy" data-toggle="tooltip" title="Classy + Light Header" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/classy.css" data-theme-navbar="navbar-default" data-theme-sidebar="">
                        <span class="section-header"></span>
                        <span class="section-side themed-background-dark-classy"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-social" data-toggle="tooltip" title="Social + Light Header" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/social.css" data-theme-navbar="navbar-default" data-theme-sidebar="">
                        <span class="section-header"></span>
                        <span class="section-side themed-background-dark-social"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-flat" data-toggle="tooltip" title="Flat + Light Header" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/flat.css" data-theme-navbar="navbar-default" data-theme-sidebar="">
                        <span class="section-header"></span>
                        <span class="section-side themed-background-dark-flat"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-amethyst" data-toggle="tooltip" title="Amethyst + Light Header" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/amethyst.css" data-theme-navbar="navbar-default" data-theme-sidebar="">
                        <span class="section-header"></span>
                        <span class="section-side themed-background-dark-amethyst"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-creme" data-toggle="tooltip" title="Creme + Light Header" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/creme.css" data-theme-navbar="navbar-default" data-theme-sidebar="">
                        <span class="section-header"></span>
                        <span class="section-side themed-background-dark-creme"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="themed-background-passion" data-toggle="tooltip" title="Passion + Light Header" data-theme="{{asset('core/themes/appui-backend/css')}}/themes/passion.css" data-theme-navbar="navbar-default" data-theme-sidebar="">
                        <span class="section-header"></span>
                        <span class="section-side themed-background-dark-passion"></span>
                        <span class="section-content"></span>
                    </a>
                </li>
            </ul>

        </div>
    </div>  	  
</div>
@stop