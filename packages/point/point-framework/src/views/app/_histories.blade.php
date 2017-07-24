<div class="table-responsive">
    <table id="list-table" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>Date</th>
            <th>User</th>
            <th>Key</th>
            <th>Old Value</th>
            <th>New Value</th>
        </tr>
        </thead>
        <tbody>
        @foreach($histories as $history)
            <tr id="{{$history->id}}">
                <td>{{ date_format_view($history->created_at, true) }}</td>
                <td>{{ $history->user->name }}</td>
                <td>{{ $history->key }}</td>
                <td>{{ $history->old_value }}</td>
                <td>{{ $history->new_value }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
