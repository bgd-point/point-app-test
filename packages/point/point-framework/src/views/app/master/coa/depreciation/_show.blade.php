<div class="table-responsive" style="padding:20px">
    <table id="account-depreciation-datatable" class="table table-striped" style="overflow-x:visible;">
        <thead>
            <tr>
                <th align="center">#</th>
                <th>Account Fixed Assets</th>
                <th>Account Depreciation</th>
            </tr>
        </thead>
        <tbody class="manipulate-row">
        <?php $i=1;?>
        @foreach($list_account_fixed_assets as $coa)
            <tr>
                <td align="center">{{$i}}</td>
                <td><strong>{{$coa->account}}</strong></td>
                <td id="column-{{$i}}">
                    <select class="selectize" name="account_depreciation_id" id="account-depreciation-id-{{$i}}" style="width:100%" onchange="saveAccountDepreciation({{$coa->id}}, this.value, {{$i}})">
                        <option value="0">No Depreciation</option>
                        @foreach($list_account_depreciations as $account_depreciation)
                        <option value="{{$account_depreciation->id}}" @if(Point\Framework\Models\AccountDepreciation::getDepreciation($account_depreciation->id) == $coa->id) selected @endif>{{$account_depreciation->name}}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
        <?php $i++;?>
        @endforeach
        </tbody>
        <tfoot></tfoot>
    </table>
</div>

<script>

    initSelectize('.selectize');

    function saveAccountDepreciation(account_fixed_asset_id, account_depreciation_id, index){
    $.ajax({
        type: 'post',
        url: "{{URL::to('master/coa/depreciation')}}",
        data: { 
            fixed_asset_id: account_fixed_asset_id,
            depreciation_id: account_depreciation_id
        },
        success: function(data) {
            if(data.status == "failed"){
                swal('failed, please select another account depreciation');
                var selectize = $("#account-depreciation-id-"+index)[0].selectize;
                selectize.clear();
            }
        }
    });
}

</script>
