<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Account</th>
        <th>Debit</th>
        <th>Credit</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><h4 style="font-weight: bold">{{$coa_asset->name}}</h4></td>
        <td></td>
        <td></td>
    </tr>
    <?php $current_group_category = 0;?>
    @foreach($coa_asset->category as $category)
        <?php $value_open = \JournalHelper::categoryValue($category->id, $date_from, $date_to); ?>
        <?php $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to); ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
        <?php $current_group_category = $category->coa_group_category_id;?>

        <tr>
            <td><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
            <td></td>
            <td></td>
        </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            @if($category->position->debit)
            <?php $total_debit += $value ?>
            <td class="text-right">{{number_format_accounting($value)}}</td>
            <td></td>
            @else
            <?php $total_credit += $value ?>
            <td></td>
            <td class="text-right">{{number_format_accounting($value)}}</td>
            @endif
        </tr>
    @endforeach

    <tr>
        <td><h4 style="font-weight: bold">{{$coa_liability->name}}</h4></td>
        <td></td>
        <td></td>
    </tr>
    @foreach($coa_liability->category as $category)
        <?php $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to); ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
            <?php $current_group_category = $category->coa_group_category_id;?>

            <tr>
                <td><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
                <td></td>
                <td></td>
            </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            @if($category->position->debit)
                <?php $total_debit += $value ?>
                <td class="text-right">{{number_format_accounting($value)}}</td>
                <td></td>
            @else
                <?php $total_credit += $value ?>
                <td></td>
                <td class="text-right">{{number_format_accounting($value)}}</td>
            @endif
        </tr>
    @endforeach

    <tr>
        <td><h4 style="font-weight: bold">{{$coa_equity->name}}</h4></td>
        <td></td>
        <td></td>
    </tr>
    @foreach($coa_equity->category as $category)
        <?php $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to); ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
            <?php $current_group_category = $category->coa_group_category_id;?>

            <tr>
                <td><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
                <td></td>
                <td></td>
            </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            @if($category->position->debit)
                <?php $total_debit += $value ?>
                <td class="text-right">{{number_format_accounting(\JournalHelper::categoryValue($category->id, $date_from, $date_to))}}</td>
                <td></td>
            @else
                <?php $total_credit += $value ?>
                <td></td>
                <td class="text-right">{{number_format_accounting(\JournalHelper::categoryValue($category->id, $date_from, $date_to))}}</td>
            @endif
        </tr>
    @endforeach

    <tr>
        <td><h4 style="font-weight: bold">{{$coa_revenue->name}}</h4></td>
        <td></td>
        <td></td>
    </tr>
    @foreach($coa_revenue->category as $category)
        <?php $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to); ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
            <?php $current_group_category = $category->coa_group_category_id;?>

            <tr>
                <td><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
                <td></td>
                <td></td>
            </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            @if($category->position->debit)
                <?php $total_debit += $value ?>
                <td class="text-right">{{number_format_accounting(\JournalHelper::categoryValue($category->id, $date_from, $date_to))}}</td>
                <td></td>
            @else
                <?php $total_credit += $value ?>
                <td></td>
                <td class="text-right">{{number_format_accounting(\JournalHelper::categoryValue($category->id, $date_from, $date_to))}}</td>
            @endif
        </tr>
    @endforeach

    <tr>
        <td><h4 style="font-weight: bold">{{$coa_expense->name}}</h4></td>
        <td></td>
        <td></td>
    </tr>
    @foreach($coa_expense->category as $category)
        <?php $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to); ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
            <?php $current_group_category = $category->coa_group_category_id;?>
            <tr>
                <td colspan="3"><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
            </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            @if($category->position->debit)
                <?php $total_debit += $value ?>
                <td class="text-right">{{number_format_accounting(\JournalHelper::categoryValue($category->id, $date_from, $date_to))}}</td>
                <td></td>
            @else
                <?php $total_credit += $value ?>
                <td></td>
                <td class="text-right">{{number_format_accounting(\JournalHelper::categoryValue($category->id, $date_from, $date_to))}}</td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
