<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><h4 style="font-weight: bold">{{$coa_asset->name}}</h4></td>
        <td></td>
    </tr>
    <?php $current_group_category = 0;?>
    @foreach($coa_asset->category as $category)
        <?php
            $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to);
            $total_asset += $value;
        ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
            <?php $current_group_category = $category->coa_group_category_id;?>

            <tr>
                <td><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
                <td></td>
            </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            <td class="text-right">{{number_format_accounting($value)}}</td>
        </tr>
    @endforeach


    <tr>
        <td><h2>TOTAL ASSET</h2></td>
        <td class="text-right">{{number_format_accounting($total_asset)}}</td>
    </tr>

    <tr>
        <td></td>
        <td></td>
    </tr>


    <tr>
        <td><h4 style="font-weight: bold">{{$coa_liability->name}}</h4></td>
        <td></td>
    </tr>
    @foreach($coa_liability->category as $category)
        <?php
            $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to);
            $total_liability += $value;
        ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
            <?php $current_group_category = $category->coa_group_category_id;?>
            <tr>
                <td><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
                <td></td>
            </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            <td class="text-right">{{number_format_accounting($value)}}</td>
        </tr>
    @endforeach

    <tr>
        <td><h4 style="font-weight: bold">{{$coa_equity->name}}</h4></td>
        <td></td>
    </tr>
    @foreach($coa_equity->category as $category)
        <?php
            $value = \JournalHelper::categoryValue($category->id, $date_from, $date_to);
            $total_equity += $value;
        ?>

        @if($category->groupCategory && $current_group_category != $category->coa_group_category_id)
            <?php $current_group_category = $category->coa_group_category_id;?>
            <tr>
                <td><h5 style="font-weight: bold">{{$category->groupCategory->account}}</h5></td>
                <td></td>
            </tr>
        @endif
        <tr>
            <td>{{$category->account}}</td>
            <td class="text-right">{{number_format_accounting($value)}}</td>
        </tr>
    @endforeach

    <tr>
        <td><h2>TOTAL EQUITY & LIABILITY</h2></td>
        <td class="text-right">{{number_format_accounting($total_liability + $total_equity)}}</td>
    </tr>
    </tbody>
</table>
