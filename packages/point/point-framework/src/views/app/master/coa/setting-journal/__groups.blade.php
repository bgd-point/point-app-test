@foreach($groups as $group)
<div class="form-group">
    <span class="col-md-3 control-label">
        {{ $group->name }}
    </span>
    <div class="col-md-6">
        <select class="selectize groups-selectize" style="width: 100%;" data-placeholder="Select Account .." onchange="updateCoa({{ $group->id }}, this.value)">
            <option></option>
            @foreach($list_coa as $coa_account)
                <option value="{{$coa_account->id}}" @if($group->coa_id == $coa_account->id) selected @endif>{{$coa_account->account}}</option>
            @endforeach
        </select>
    </div>
</div>
@endforeach
