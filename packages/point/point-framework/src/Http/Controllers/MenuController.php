<?php

namespace Point\Framework\Http\Controllers;

class MenuController extends Controller
{
    public function inventory()
    {
        return view('framework::app.inventory.menu', compact('project'));
    }

    public function expedition()
    {
        return view('framework::app.expedition.menu', compact('project'));
    }

    public function purchasing()
    {
        return view('framework::app.purchasing.menu', compact('project'));
    }

    public function sales()
    {
        return view('framework::app.sales.menu', compact('project'));
    }

    public function manufacture()
    {
        return view('framework::app.manufacture.menu', compact('project'));
    }

    public function finance()
    {
        return view('framework::app.finance.menu', compact('project'));
    }

    public function accounting()
    {
        return view('framework::app.accounting.menu', compact('project'));
    }
}
