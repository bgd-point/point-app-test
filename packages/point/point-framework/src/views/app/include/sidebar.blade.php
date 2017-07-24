@if(auth()->user()->may('menu.inventory'))
<li id="sidebar-menu-inventory">
    <a href="{{url('/inventory')}}" class="{{\Request::segment(1)=='inventory'?'active':''}}"><i class="fa fa-cubes sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Inventory</span></a>
</li>
@endif
@if(auth()->user()->may('menu.purchasing'))
<li id="sidebar-menu-purchasing">
    <a href="{{url('/purchasing')}}" class="{{\Request::segment(1)=='purchasing'?'active':''}}"><i class="fa fa-cart-arrow-down sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Purchasing</span></a>
</li>
@endif
@if(auth()->user()->may('menu.sales'))
<li id="sidebar-menu-sales">
    <a href="{{url('/sales')}}" class="{{\Request::segment(1)=='sales'?'active':''}}"><i class="fa fa-shopping-cart sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Sales</span></a>
</li>
@endif
@if(auth()->user()->may('menu.expedition'))
    <li id="sidebar-menu-expedition">
        <a href="{{url('/expedition')}}" class="{{\Request::segment(1)=='expedition'?'active':''}}"><i class="fa fa-truck sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Expedition</span></a>
    </li>
@endif
@if(auth()->user()->may('menu.manufacture'))
<li id="sidebar-menu-manufacture">
    <a href="{{url('/manufacture')}}" class="{{\Request::segment(1)=='manufacture'?'active':''}}"><i class="fa fa-gears sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Manufacture</span></a>
</li>
@endif
@if(auth()->user()->may('menu.finance'))
<li id="sidebar-menu-finance">
    <a href="{{url('/finance')}}" class="{{\Request::segment(1)=='finance'?'active':''}}"><i class="fa fa-calculator sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Finance</span></a>
</li>
@endif
@if(auth()->user()->may('menu.accounting'))
<li id="sidebar-menu-accounting">
    <a href="{{url('/accounting')}}" class="{{\Request::segment(1)=='accounting'?'active':''}}"><i class="fa fa-file-excel-o sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Accounting</span></a>
</li>
@endif

