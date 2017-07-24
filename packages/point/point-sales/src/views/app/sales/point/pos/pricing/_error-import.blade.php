<div id="modal-import-error" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>Error Import</strong></h3>
            </div>
            <div class="modal-body">
            <div class="well-sm bg-info">
                <label for="example-nf-email">Information</label>
                <p>
                    the error occurs because the items and group customers not found in database
                </p>
            </div>    
            @if(!empty($error))
                <div class="table-responsive"> 
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Group</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count=0;?>
                            
                            @for($i=0; $i < count($error); $i++)
                                <input type="hidden" id="item-id-{{$i}}" value="{{$error[$i]['id']}}">
                                <tr>
                                    <td align="center">{{$i+1}}</td>
                                    <td id="item-{{$i}}">{{$error[$i]['item']}}</td>
                                    <td id="price-{{$i}}">{{number_format_quantity($error[$i]['price'])}}</td>
                                    <td id="discount-{{$i}}">{{$error[$i]['discount']}}</td>
                                    <td id="group-{{$i}}">{{$error[$i]['group']}}</td>
                                </tr>
                            @endfor
                        
                        </tbody>
                    </table> 
                </div>
            @endif
            </div>
            <div class="modal-footer">
                <a href="{{url('sales/point/pos/pricing/import/clear-error-temp')}}" class="btn btn-effect-ripple btn-primary">Clear</a>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
