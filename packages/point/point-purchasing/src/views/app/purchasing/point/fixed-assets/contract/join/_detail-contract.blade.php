@if(!$contract)
<h3 class="">Data not found</h3>
<?php return false; ?>
@endif

<div class="form-horizontal form-bordered">
    <div class="form-group">
        <label class="col-md-3 control-label">Form Number</label>
        <div class="col-md-6 content-show">
            {{$contract->formulir->form_number}}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Asset Account *</label>
        <div class="col-md-6 content-show">
            {{$contract->coa->name}}
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-md-3 control-label">Acquisition date *</label>
        <div class="col-md-6 content-show">
            {{date_format_view($contract->formulir->form_date)}}
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label">Asset name *</label>
        <div class="col-md-6 content-show">
            {{$contract->codeName}}
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label">Useful period *</label>
        <div class="col-md-6 content-show">
            {{$contract->useful_life}} Month
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Salvage Value *</label>
        <div class="col-md-6 content-show">
            {{number_format_quantity($contract->salvage_value, 0)}}
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label">Purchase date *</label>
        <div class="col-md-6 content-show">
            {{date_format_view($contract->date_purchased)}}
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label">Supplier</label>
        <div class="col-md-6 content-show">
            {{$contract->coa->name}}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Quantity *</label>
        <div class="col-md-6 content-show">
            {{$contract->supplier->codeName}}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Price *</label>
        <div class="col-md-6 content-show">
            {{number_format_quantity($contract->price, 0)}}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Total price *</label>
        <div class="col-md-6 content-show">
            {{number_format_quantity($contract->total_price, 0)}}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Total paid </label>
        <div class="col-md-6 content-show">
            {{number_format_quantity($contract->total_paid, 0)}}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">depreciation *</label>
        <div class="col-md-6 content-show">
            {{number_format_quantity($contract->depreciation)}} Month
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-md-3 control-label">notes</label>
        <div class="col-md-6 content-show">
            {{$contract->formulir->notes}}
        </div>
    </div>
</div>

