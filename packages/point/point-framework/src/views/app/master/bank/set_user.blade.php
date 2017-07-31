@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/bank') }}">Bank</a></li>
            <li>Set User</li>
        </ul>

        <h2 class="sub-header">Bank</h2>

        @include('framework::app.master.bank._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <fieldset>
                        <legend><i class="fa fa-angle-right"></i> Set User Bank</legend>
                        <div class="form-group">
                            <div class="col-md-9">
                                <span class="help-block">
                                    Set user related bank, when user is linked to the bank, then every transaction will be related to this bank
                                </span>
                            </div>
                        </div>
                    </fieldset>

                    @foreach($list_user as $user)
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            {{ $user->name }}
                        </label>
                        <div class="col-md-6">
                            <select id="bank-id" name="bank_id" class="selectize" style="width: 100%;" data-placeholder="Select Bank .." onchange="updateBank({{$user->id}}, this.value)">
                                <option></option>
                                @foreach($list_bank as $bank)
                                    <option value="{{$bank->id}}" @if(\Point\Framework\Models\Master\UserBank::getBank($user->id) == $bank->id) selected @endif>{{$bank->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <a href="{{ url('master/bank') }}" class="btn btn-effect-ripple btn-primary">Done</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        function updateBank(user_id, bank_id){
            $.ajax({
                url: "{{URL::to('master/bank/set-user')}}",
                type: 'POST',
                data: {
                    user_id: user_id,
                    bank_id: bank_id
                },
                success: function(data) {
                    notification('success', 'Bank has been set');
                },error: function(data){
                    notification('Failed', 'Something went wrong');
                }
            });
        }
    </script>
@stop
