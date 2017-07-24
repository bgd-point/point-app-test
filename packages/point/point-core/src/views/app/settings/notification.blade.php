@extends('core::app.layout')

@section('content')
<div id="page-content" class="inner-sidebar-left">
    @include('core::app.settings._sidebar')
    @include('core::app.error._alert')
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="sub-header">Notification</h2>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="col-md-6">
                                <button class="btn btn-primary" onclick="notifyMe()">Enable Desktop Notification</button>
                            </div>
                        </div>
                    </div>     
                </div> 
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
    /*
     * EXAMPLE CALL DESKTOP NOTIFICATION
     */
    
    // var options = {
    //   body: "Body Text",
    //   icon: "icon.jpg",
    //   dir : "ltr"
    // };

    // var notification = new Notification("Title",options);
    
    // notification.onclick = function() {
    //     window.location = '{{url('/')}}';
    // }

          
    function notifyMe() {
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notification");
        }
        else if (Notification.permission === "granted") {
            var options = {
                    body: "Desktop Notification Aktif",
                    icon: "{{asset('assets/img/logo-icon-amethys.jpg')}}",
                    dir : "ltr"
                 };
            var notification = new Notification("Success",options);
        }
        else if (Notification.permission !== 'denied') {
            Notification.requestPermission(function (permission) {
              if (!('permission' in Notification)) {
                Notification.permission = permission;
              }

              if (permission === "granted") {
                var options = {
                    body: "Desktop Notification Aktif",
                    icon: "{{asset('assets/img/logo-icon-amethys.jpg')}}",
                    dir : "ltr"
                  };
                var notification = new Notification("Success",options);
              }
            });
        }
    }
</script>

@stop
