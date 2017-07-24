@extends('core::app.layout')

@section('scripts')
<script type="text/javascript">
function updateConfig(value, url)
{
    $.ajax({
        url: url,
        type: 'GET',
        data: {
            value: value
        },
        success: function(data) {
            notification(data['title'], data['msg']);
        }, error: function(data) {
            notification(data['title'], data['msg']);
        }
    });
}
</script>
@stop

@section('content')
<div id="page-content" class="inner-sidebar-left">

     @include('core::app.settings._sidebar')
 
    <div class="panel panel-default"> 
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="sub-header">Config</h2>
                    <form action="" method="post" class="form-horizontal form-bordered" onsubmit="return false;">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date Input</label>
                            <div class="col-md-9">
                                <div class="radio">
                                    <label for="date-input1">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-input')}}')" type="radio" id="date-input1" name="date-input" value="d/m/Y" @if($setting_date_input->name == "date-input" && $setting_date_input->value == 'd/m/Y') checked @endif> 31/12/2015
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-input2">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-input')}}')" type="radio" id="date-input2" name="date-input" value="d/m/y" @if($setting_date_input->name == "date-input" && $setting_date_input->value == 'd/m/y') checked @endif> 31/12/15
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-input3">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-input')}}')" type="radio" id="date-input3" name="date-input" value="d-m-Y" @if($setting_date_input->name == "date-input" && $setting_date_input->value == 'd-m-Y') checked @endif> 31-12-2015
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-input4">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-input')}}')" type="radio" id="date-input4" name="date-input" value="d-m-y" @if($setting_date_input->name == "date-input" && $setting_date_input->value == 'd-m-y') checked @endif> 31-12-15
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Date Show</label>
                            <div class="col-md-9">
                                <div class="radio">
                                    <label for="date-show1">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-show')}}')" type="radio" id="date-show1" name="date-show" value="d/m/Y" @if($setting_date_show->name == "date-show" && $setting_date_show->value == 'd/m/Y') checked @endif> 31/12/2015
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-show2">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-show')}}')" type="radio" id="date-show2" name="date-show" value="d/m/y" @if($setting_date_show->name == "date-show" && $setting_date_show->value == 'd/m/y') checked @endif> 31/12/15
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-show3">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-show')}}')" type="radio" id="date-show3" name="date-show" value="d-m-Y" @if($setting_date_show->name == "date-show" && $setting_date_show->value == 'd-m-Y') checked @endif> 31-12-2015
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-show4">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-show')}}')" type="radio" id="date-show4" name="date-show" value="d-m-y" @if($setting_date_show->name == "date-show" && $setting_date_show->value == 'd-m-y') checked @endif> 31-12-15
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-show5">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-show')}}')" type="radio" id="date-show5" name="date-show" value="d M Y" @if($setting_date_show->name == "date-show" && $setting_date_show->value == 'd M Y') checked @endif> 31 Dec 2015
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-show6">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-show')}}')" type="radio" id="date-show6" name="date-show" value="d F Y"  @if($setting_date_show->name == "date-show" && $setting_date_show->value == 'd F Y') checked @endif> 31 December 2015
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="date-show7">
                                        <input onclick="updateConfig(this.value, '{{url('settings/config/date-show')}}')" type="radio" id="date-show7" name="date-show" value="Y, d M"  @if($setting_date_show->name == "date-show" && $setting_date_show->value == 'Y, d M') checked @endif> 2015, 31 Dec
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Allow Mouse Select</label>
                            <div class="col-md-9">
                                <div class="checkbox">
                                    <label for="mouse-select-allowed">
                                        <input onclick="updateConfig(this.checked, '{{url('settings/config/mouse-select-allowed')}}')" type="checkbox" id="mouse-select-allowed" name="mouse-select-allowed" @if($setting_mouse_select_allowed->value == "true") checked @endif>
                                    </label>
                                    <span class="help-block">{{$setting_mouse_select_allowed->notes}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Allow Mouse Right Click</label>
                            <div class="col-md-9">
                                <div class="checkbox">
                                    <label for="right-click-allowed">
                                        <input onclick="updateConfig(this.checked, '{{url('settings/config/right-click-allowed')}}')" type="checkbox" id="right-click-allowed" name="right-click-allowed" @if($setting_right_click_allowed->value == "true") checked @endif>
                                    </label>
                                    <span class="help-block">{{$setting_right_click_allowed->notes}}</span>
                                </div> 
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Allow User Change Password</label>
                            <div class="col-md-9">
                                <div class="checkbox">
                                    <label for="user-change-password-allowed">
                                        <input onclick="updateConfig(this.checked, '{{url('settings/config/user-change-password-allowed')}}')" type="checkbox" id="user-change-password-allowed" name="user-change-password-allowed" @if($setting_user_change_password_allowed->value == "true") checked @endif>
                                    </label>
                                    <span class="help-block">{{$setting_user_change_password_allowed->notes}}</span>
                                </div> 
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Font Size</label>
                            <div class="col-md-9">
                                <label for="pos-font-size">
                                    <select name="pos-font-size" id="pos-font-size" class="selectize" onchange="updateConfig(this.value, '{{url('settings/config/pos-font-size')}}')">
                                        <option value="12" @if($setting_pos_font_size->value == 12) selected @endif>12 px</option>
                                        <option value="14" @if($setting_pos_font_size->value == 14) selected @endif>14 px</option>
                                        <option value="16" @if($setting_pos_font_size->value == 16) selected @endif>16 px</option>
                                        <option value="18" @if($setting_pos_font_size->value == 18) selected @endif>18 px</option>
                                        <option value="20" @if($setting_pos_font_size->value == 20) selected @endif>20 px</option>
                                    </select>
                                </label>
                                <span class="help-block">{{$setting_pos_font_size->notes}}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Activate User Guide Helper</label>
                            <div class="col-md-9">
                                <div class="checkbox">
                                    <label for="user-guide-helper">
                                        <input onclick="updateConfig(this.checked, '{{url('settings/config/user-guide-helper')}}')" type="checkbox" id="user-guide-helper" name="user-guide-helper" @if($setting_user_guide_helper->value == "true") checked @endif>
                                    </label>
                                    <span class="help-block">{{$setting_user_guide_helper->notes}}</span>
                                </div>
                            </div>
                        </div>
                    </form>     
                </div> 
            </div>
        </div>
    </div>  
</div>
@stop
