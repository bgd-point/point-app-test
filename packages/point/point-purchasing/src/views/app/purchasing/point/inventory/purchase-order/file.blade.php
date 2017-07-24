@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-order') }}">Purchase Order</a></li>
            <li>Download</li>
        </ul>
        <h2 class="sub-header">Purchase Order </h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-order._menu')

        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-12">
                    <a href="{{url('purchasing/point/purchase-order/'.$purchase_order->formulir_id)}}"
                       class="btn btn-primary btn-block">BACK TO FORM</a>
                </div>
                <hr/>
                <div class="col-sm-12">
                    <div class="dropzone" id="dropzone">
                        <div class="fallback">
                            <input type="file" name="file" multiple/>
                        </div>
                    </div>
                </div>
                <hr/>
            </div>
            <div class="col-sm-9">
                @foreach(\Storage::allFiles('client/'.config('point.client.slug').'/purchase-order/'.str_replace('/','-',$purchase_order->form_number)) as $file)

                    <div class="col-sm-6 col-lg-4" style="display: block;">
                        <div class="media-items animation-fadeInQuick2" data-category="zip">
                            <div class="media-items-options text-right">
                                <a href="{{url('storage/download/purchase-order/'.$purchase_order->formulir_id.'/'.basename($file))}}"
                                   class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a>
                                <a href="{{url('storage/delete/purchase-order/'.$purchase_order->formulir_id.'/'.basename($file))}}"
                                   onclick="return confirm('apakah anda ingin menghapus file ini')"
                                   class="btn btn-xs btn-danger"><i class="fa fa-times"></i></a>
                            </div>
                            <div class="media-items-content">
                                <i class="fa fa-file-archive-o fa-5x text-muted"></i>
                            </div>
                            <h4>
                                <h5>{{basename($file)}}</h5>
                                <h6>{{date('d M Y H:i', \Storage::lastModified($file))}}</h6>
                                <small>{{ number_format_quantity(\Storage::size($file) / 1024) }} KB</small>
                            </h4>
                        </div>
                    </div>

                @endforeach

            </div>
        </div>
        <br/><br/>

        <div class="row">

        </div>

    </div>
@stop

@section('scripts')
    <script>
        $myDropzone = $("#dropzone").dropzone({
            url: "/storage/upload/purchase-order/{{$purchase_order->formulir_id}}",
            maxFilesize: 5,
            parallelUploads: 10,
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            init: function () {
                var thisDropzone = this;

                this.on("queuecomplete", function (files, response) {
                    // alert('upload complete');
                    location.reload();
                });
            }
        });

        function download(link) {
            $.ajax({
                url: "{{URL::to('storage/download')}}",
                type: 'GET',
                data: {
                    link: link
                },
                success: function (data) {

                }, error: function (data) {

                }
            });
        }
    </script>
@stop
