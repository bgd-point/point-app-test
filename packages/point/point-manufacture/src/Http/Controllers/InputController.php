<?php

namespace Point\PointManufacture\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Warehouse;
use Point\PointManufacture\Vesa\InputAfterAprrovalVesa;
use Point\PointManufacture\Helpers\ManufactureHelper;
use Point\PointManufacture\Http\Requests\InputRequest;
use Point\PointManufacture\Models\Formula;
use Point\PointManufacture\Models\InputProcess;
use Point\PointManufacture\Models\Machine;
use Point\PointManufacture\Models\Process;

class InputController extends Controller
{
    use ValidationTrait;

    public function __construct()
    {
        View::share([
            'list_process' => Process::active()->get(),
            'list_warehouse' => Warehouse::active()->get(),
            'list_machine' => Machine::active()->get(),
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
    public function index($process_id)
    {
        access_is_allowed('read.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.index');
        $view->list_input = InputProcess::joinFormulir()
            ->joinMachine()
            ->notArchived()
            ->where('process_id', $process_id)
            ->search(
                app('request')->input('order_by'),
                app('request')->input('order_type'),
                app('request')->input('status'),
                app('request')->input('date_from'),
                app('request')->input('date_to'),
                app('request')->input('search')
            )
            ->selectOriginal()
            ->paginate(100);
        $view->process = Process::find($process_id);

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($process_id)
    {
        access_is_allowed('create.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.create');
        $view->process = Process::find($process_id);

        return $view;
    }

    public function chooseFormula($process_id)
    {
        access_is_allowed('read.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.choose-formula');
        $view->list_formula = Formula::joinFormulir()
            ->notArchived()
            ->approvalApproved()
            ->search(
                null,
                null,
                null,
                app('request')->input('date_from'),
                app('request')->input('date_to'),
                app('request')->input('search')
            )
            ->selectOriginal()
            ->paginate(100);
        $view->process = Process::find($process_id);
        return $view;
    }

    public function useFormula($process_id, $id)
    {
        access_is_allowed('create.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.use-formula');
        $view->formula = Formula::find($id);
        $view->list_input_archived = Formula::joinFormulir()
            ->notArchived()
            ->approvalApproved()
            ->selectOriginal()
            ->paginate(100);
        $view->process = Process::find($process_id);
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(InputRequest $request, $process_id)
    {
        formulir_is_allowed_to_create('create.point.manufacture.input', date_format_db(app('request')->input('form_date')), []);

        DB::beginTransaction();

        $formulir = formulir_create($request->input(), 'point-manufacture-input');
        $input_process = ManufactureHelper::createInput($request, $formulir);
        timeline_publish('create.point.manufacture.input', 'create manufacture input ' . $formulir->form_number);

        DB::commit();

        gritter_success('create "' . $formulir->form_number . '" success');
        return redirect('manufacture/point/process-io/' . $input_process->process_id . '/input/' . $input_process->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($process_id, $id)
    {
        access_is_allowed('read.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.show');
        $view->process = Process::find($process_id);
        $view->input = InputProcess::find($id);
        $view->list_input_archived = InputProcess::selectArchived($view->input->formulir->form_number);
        $view->revision = $view->list_input_archived->count();

        return $view;
    }

    public function archived($process_id, $id)
    {
        access_is_allowed('read.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.archived');

        $view->process = Process::find($process_id);
        $view->input_archived = InputProcess::find($id);
        $view->input = InputProcess::selectNotArchived($view->input_archived->archived);

        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($process_id, $id)
    {
        access_is_allowed('update.point.manufacture.input');

        $view = view('point-manufacture::app.manufacture.point.input.edit');

        $view->process = Process::find($process_id);
        $view->input = InputProcess::find($id);

        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(InputRequest $request, $process_id, $id)
    {
        $input = InputProcess::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.manufacture.input', date_format_db($request->input('form_date'), $request->input('time')), $input->formulir);

        DB::beginTransaction();

        $formulir_old = FormulirHelper::archive($request->input(), $input->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $input_process = ManufactureHelper::createInput($request, $formulir);
        timeline_publish('update.point.manufacture.input', 'upadate manufacture input ' .$formulir->form_number);

        DB::commit();

        gritter_success('update "' . $formulir->form_number . '" success');
        return redirect('manufacture/point/process-io/' . $input_process->process_id . '/input/' . $input_process->id);
    }
}
