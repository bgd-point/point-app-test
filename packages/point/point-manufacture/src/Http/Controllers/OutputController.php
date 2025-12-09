<?php

namespace Point\PointManufacture\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Warehouse;
use Point\PointManufacture\Helpers\ManufactureHelper;
use Point\PointManufacture\Models\InputProcess;
use Point\PointManufacture\Models\Machine;
use Point\PointManufacture\Models\OutputProcess;
use Point\PointManufacture\Models\Process;

class OutputController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($process_id)
    {
        access_is_allowed('read.point.manufacture.output');

        $view = view('point-manufacture::app.manufacture.point.output.index');

        $view->list_output = OutputProcess::joinFormulir()
            ->joinInput()
            ->join('point_manufacture_machine', 'point_manufacture_machine.id', '=', 'point_manufacture_output.machine_id');
            ->notArchived()
            ->search(app('request')->input('date_from'),
                app('request')->input('date_to'),
                app('request')->input('search'))
            ->where('process_id', $process_id)
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
    public function createStep1($process_id)
    {
        access_is_allowed('create.point.manufacture.output');

        $view = view('point-manufacture::app.manufacture.point.output.create-step-1');
        $view->list_input = InputProcess::joinFormulir()->notArchived()->open()->approvalApproved()->selectOriginal()->paginate(100);
        $view->process = Process::find($process_id);

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $process_id
     * @param $input_id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createStep2($process_id, $input_id)
    {
        access_is_allowed('create.point.manufacture.output');

        $view = view('point-manufacture::app.manufacture.point.output.create-step-2');
        $view->input = InputProcess::find($input_id);
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_machine = Machine::active()->get();
        $view->process = Process::find($process_id);

        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $process_id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'machine_id' => 'required'
        ]);

        formulir_is_allowed_to_create('create.point.manufacture.output', date_format_db(app('request')->input('form_date')), []);

        DB::beginTransaction();

        $formulir = formulir_create($request->input(), 'point-manufacture-output');
        $output = ManufactureHelper::createOutput($request, $formulir);
        timeline_publish('create.point.manufacture.output', 'create manufacture output ' . $formulir->form_number);

        DB::commit();

        gritter_success('create process manufacture output "' . $formulir->form_number . '" success');
        return redirect('manufacture/point/process-io/' . $output->input->process_id . '/output');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($process_id, $id)
    {
        access_is_allowed('read.point.manufacture.output');

        $view = view('point-manufacture::app.manufacture.point.output.show');

        $view->process = Process::find($process_id);
        $view->output = OutputProcess::find($id);
        $view->input = InputProcess::find($view->output->input_id);
        $view->list_output_archived = OutputProcess::selectArchived($view->output->formulir->form_number);
        $view->revision = $view->list_output_archived->count();

        return $view;
    }

    /**
     * Display list of archived resource
     *
     * @param $process_id
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function archived($process_id, $id)
    {
        access_is_allowed('read.point.manufacture.output');

        $view = view('point-manufacture::app.manufacture.point.output.archived');
        $view->process = Process::find($process_id);
        $view->output_archived = OutputProcess::find($id);
        $view->output = OutputProcess::selectNotArchived($view->output_archived->archived);

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
        access_is_allowed('update.point.manufacture.output');

        $view = view('point-manufacture::app.manufacture.point.output.edit');

        $view->process = Process::find($process_id);
        $view->output = OutputProcess::find($id);
        $view->input = InputProcess::find($view->output->input_id);
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_machine = Machine::active()->get();

        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $process_id, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'machine_id' => 'required',
        ]);

        $formulir_check = OutputProcess::find($id)->formulir;

        formulir_is_allowed_to_update('update.point.manufacture.output', $formulir_check->form_date, $formulir_check);

        DB::beginTransaction();

        formulir_archive($request->input(), $formulir_check->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_check->form_number, $formulir_check->form_raw_number);
        $output = ManufactureHelper::createOutput($request, $formulir);
        timeline_publish('update.point.manufacture.output', 'update process manufacture output ' . $formulir->form_number);

        DB::commit();

        gritter_success('update process manufacture output "' . $formulir->form_number . '" success');
        return redirect('manufacture/point/process-io/' . $output->input->process_id . '/output/' . $output->id);
    }
}
