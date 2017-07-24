@extends('core::app.layout')

@section('content')
<div id="page-content" class="inner-sidebar-left">

   @include('core::app.settings._sidebar')

   @include('core::app.error._alert')

   <div class="panel panel-default"> 
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="sub-header">Company Logo</h2>
                    <div class="form-horizontal">
                        <form class="form-group" action="{{url('settings/logo/insert')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <?php
                            $path = public_path('uploads/'.app('request')->project->url . '/logo/logo.png');
                            ?>
                            <input type="file" name="logo"  id="logo" style="display:none"/>
                            <div class="wrap-logo text-center" onclick="$('#logo').click();load()" @if(file_exists($path)) style="width: 250px;" @else style="width: 250px; height: 250px;" @endif >
                                <div class="text-logo">@if(!file_exists($path)) choose image @endif</div>
                                <img id="prev-logo" name="prev-logo" @if(file_exists($path)) src="{{url_logo()}}" @else src="" style="display:none" class="img" @endif alt="   " />
                            </div>
                            <button type="submit" id="btn-submit" class="btn btn-primary btn-sm m-l" style="display:none">Submit</button>
                            <span class="m-l" style="font-size:12px"> <i>*click image for change logo</i></span>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<style type="text/css">
    .m-l { margin-left: 20px; margin-top: 20px}
    .wrap-logo {
        border: 2px dotted #000; margin-left: 20px; padding : 20px;
    }
    .img {
        width: 210px; height: 210px; padding : auto;
    }
</style>

<script type="text/javascript">
    function load(){
        $("#logo").change(function() {
            var preview = document.querySelector('#prev-logo');
            var file    = document.querySelector('#logo').files[0];
            var reader  = new FileReader();
            // check format file images
            var imagefile = file.type;
            var match= ["image/jpeg","image/png","image/jpg"]; 

            if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
            {
                $('#prev-logo').attr('src','');
                $('#prev-logo').removeClass('img');
                $('.text-logo').html('format not supported, format file must be jpeg, jpg or png');
                $('#btn-submit').css('display','none');
            }else{
                $('#prev-logo').addClass('img');
                $('#prev-logo').css('display','block');
                $('#btn-submit').css('display','block');
                $('.text-logo').html('');
                reader.onloadend = function () {
                    preview.src = reader.result;
                }

                if (file) {
                    reader.readAsDataURL(file);
                } else {
                    preview.src = "";
                }
            }

        }); 
    }
</script>
@stop
