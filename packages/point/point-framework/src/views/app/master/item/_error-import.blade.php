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
                    the error occurs because the name of item duplicate, asset account, category, warehouse, and unit item not found in database
                </p>
            </div>    
            @if(!empty($error))
                <div class="table-responsive"> 
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th>Asset Account</th>
                                <th>Category</th>
                                <th>Warehouse</th>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Cost of Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count=0;?>
                            
                            @for($i=0; $i < count($error); $i++)
                                <input type="hidden" id="item-name-{{$i}}" value="{{$error[$i]['name']}}">
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{$error[$i]['asset_account']}}</td>
                                    <td>{{$error[$i]['category']}}</td>
                                    <td>{{$error[$i]['warehouse']}}</td>
                                    <td>{{$error[$i]['name']}}</td>
                                    <td>{{$error[$i]['quantity']}}</td>
                                    <td>{{$error[$i]['unit']}}</td>
                                    <td>{{$error[$i]['cost_of_sale']}}</td>
                                    
                                </tr>
                            @endfor
                        
                        </tbody>
                    </table> 

                    
                </div>
            @endif
            </div>
            <div class="modal-footer">
            <a href="{{url('master/item/import/clear-error-temp')}}" class="btn btn-effect-ripple btn-primary">Clear</a>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
