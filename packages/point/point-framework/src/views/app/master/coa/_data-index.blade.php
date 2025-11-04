<div class="panel-body">
    <?php $counter=1;?>
    @foreach($list_coa_position as $coa_position)
        <?php ++$counter;?>
        <div class="panel-heading header-{{$counter}}" onmouseenter="showHeader('.header-{{$counter}}')" onmouseleave="hideHeader('.header-{{$counter}}')">
            <h4 class="panel-title">
                <a data-toggle="collapse" href="#position-{{$coa_position->id}}">
                    <i class="fa fa-caret-down"></i>
                </a>
                <a href="javascript:void(0)" onclick="createCategory({{$coa_position->id}}, '{{$coa_position->name}}')">
                    <strong style="margin-left:10px" >{{$coa_position->name}}</strong>
                </a>
            </h4>
        </div>

        @if($coa_position->category->count())
            <div id="position-{{$coa_position->id}}" class="panel-collapse collapse in" style="border:none;">
                <ul class="list-group" style="margin-left:20px;border-left:1px dotted grey;" >
                    @foreach($coa_position->category as $coa_category)
                        <?php ++$counter;?>
                        <div class="list-group-item hoverable" style="border:none" id="coa_category-{{$counter}}">
                            @if($coa_category->group->count() || $coa_category->coa->count())
                                <a data-toggle="collapse" href="#category-{{$coa_category->id}}"><i class="fa fa-caret-down"></i></a>
                            @endif
                            <a href="javascript:void(0)" onclick="show({{$coa_category->id}}, 'category')"><span style="@if($coa_category->group->count() || $coa_category->coa->count()) margin-left:10px @else margin-left:20px @endif" id="coa-category-{{$counter}}">{{$coa_category->account}}</span></a>
                        </div>
                        @if($coa_category->group)
                            <div id="category-{{$coa_category->id}}" class="panel-collapse collapse in" style="border:none;padding-bottom: 0;margin-bottom: -20px;">
                                <ul class="list-group ul-coa-category{{$counter}}" style="margin-left:35px;border:none; margin-top:5px">
                                    @foreach($coa_category->group as $coa_group)
                                        <?php ++$counter;?>
                                        <li id="list-{{$counter}}" class="list-group-item hoverable" style="border:none;">
                                            @if($coa_group->coa->count())
                                                <a data-toggle="collapse" href="#coa_group-{{$coa_group->id}}"><i class="fa fa-caret-down"></i></a>
                                            @endif
                                            <a style="margin-left:10px" href="javascript:void(0)" onclick="show({{$coa_group->id}}, 'group')"><span id="coa-group-name-{{$counter}}" ><i class="fa fa-folder"></i> {{$coa_group->account}}</span></a>
                                        </li>
                                        @if($coa_group->coa->count())
                                            <div id="coa_group-{{$coa_group->id}}" class="panel-collapse collapse in">
                                                <ul class="list-group ul-group-{{$counter}}" style="margin-left:20px">
                                                    @foreach($coa_group->coa as $coa)
                                                        @if(!preg_match('/lioni/i', $coa->name))
                                                        <?php ++$counter;?>
                                                        <li id="list-{{$counter}}" style="margin-left:15px;border:none" class="list-group-item hoverable">
                                                            <a style="margin-left:10px" href="javascript:void(0)" onclick="show({{$coa->id}}, 'coa')"><span id="coa-name-{{$counter}}">{{$coa->account}}</span></a>
                                                        </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @endforeach

                                    @foreach($coa_category->coaWithoutGroup as $coa)
                                        @if(!preg_match('/lioni/i', $coa->name))
                                        <?php ++$counter;?>
                                        <li id="list-{{$counter}}" class="list-group-item hoverable" style="border:none">
                                            <a href="javascript:void(0)" style="margin-left:10px" onclick="show({{$coa->id}}, 'coa')"><span id="coa-name-{{$counter}}">{{$coa->account}}</span></a>
                                        </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <?php $counter++;?>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach
</div>

<script>
    $(document).ready(function(){
        initHoverable();
    });
</script>
