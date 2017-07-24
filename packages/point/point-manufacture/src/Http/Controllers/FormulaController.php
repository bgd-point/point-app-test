<?php

namespace Point\PointManufacture\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;
use Point\PointManufacture\Helpers\ManufactureHelper;
use Point\PointManufacture\Http\Requests\FormulaRequest;
use Point\PointManufacture\Models\Formula;
use Point\PointManufacture\Models\Process;

class FormulaController extends Controller
{
    /**
     * Formula is a pre-configured production process
     * Then when user start a manufacture process
     * They can use this formula as a template instead of adding again one by one
     */

    use ValidationTrait;

    public function __construct()
    {
        // Include data this to all form
        View::share([
            'list_process' => Process::active()->get(),
            'list_warehouse' => Warehouse::active()->get(),
            'list_material' => Item::active()->get(),
            'list_product' => Item::active()->get(),
            'list_user_approval' => UserHelper::getAllUser()
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.manufacture.formula');

        $view = view('point-manufacture::app.manufacture.point.formula.index');
        $view->list_formula = Formula::joinFormulir()
            ->notArchived()
            ->selectOriginal()
            ->search(
                app('request')->input('order_by'),
                app('request')->input('order_type'),
                app('request')->input('status'),
                app('request')->input('date_from'),
                app('request')->input('date_to'),
                app('request')->input('search')
            )
            ->paginate(100);

        return $view;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.manufacture.formula');

        return view('point-manufacture::app.manufacture.point.formula.create');
    }

    /**
     * Store new resource
     * @param \Point\PointManufacture\Http\Requests\FormulaRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(FormulaRequest $request)
    {
        formulir_is_allowed_to_create('create.point.manufacture.formula', date_format_db(app('request')->input('form_date')), []);

        DB::beginTransaction();

        $formulir = formulir_create($request->input(), 'point-manufacture-formula');
        ManufactureHelper::createFormula($request, $formulir);
        timeline_publish('create.point.manufacture.formula', 'create formula ' . $formulir->form_number);

        DB::commit();

        gritter_success('create formula "' . $formulir->form_number . '" success');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.manufacture.formula');

        $view = view('point-manufacture::app.manufacture.point.formula.show');

        $view->formula = Formula::find($id);
        $view->list_formula_archived = Formula::joinFormulir()->archived($view->formula->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_formula_archived->count();

        return $view;
    }

    /**
     * Display list of archived resource
     *
     * @param $id
     *
     * @return \Illuminate\Support\Facades\View
     */
    public function archived($id)
    {
        access_is_allowed('read.point.manufacture.formula');

        $view = view('point-manufacture::app.manufacture.point.formula.archived');

        $view->formula_archived = Formula::find($id);
        $view->formula = Formula::selectNotArchived($view->formula_archived->archived);

        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.point.manufacture.formula');

        $view = view('point-manufacture::app.manufacture.point.formula.edit');
        $view->formula = Formula::find($id);

        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(FormulaRequest $request, $id)
    {
        $formulir_check = Formulir::find($id);
        formulir_is_allowed_to_update('update.point.manufacture.formula', $formulir_check->form_date, $formulir_check);

        DB::beginTransaction();

        formulir_archive($request->input(), $formulir_check->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_check->form_number, $formulir_check->form_raw_number);
        ManufactureHelper::createFormula($request, $formulir);
        timeline_publish('update.point.manufacture.formula', 'update formula ' . $formulir->form_number);

        DB::commit();

        gritter_success('update formula "' . $formulir->form_number . '" success');
        return redirect('manufacture/point/formula/' . $formulir->formulir_id);
    }
}
