<?php

namespace Point\Framework\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\PointFinance\Models\CashAdvance;

class FormulirController extends Controller
{
    use ValidationTrait;

    /**
     * Cancel form
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function cancel()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(\Auth::user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        $formulir_id = \Input::get('formulir_id');
        $permission_slug = \Input::get('permission_slug');

        DB::beginTransaction();

        try {
            FormulirHelper::cancel($permission_slug, $formulir_id);
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        DB::commit();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Canceled Form Success'
        );

        return $response;
    }

    /**
     * Close form
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function close()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(auth()->user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        $formulir_id = \Input::get('id');

        try {
            DB::beginTransaction();
            $formulir = Formulir::find($formulir_id);
            $formulir->form_status = 1;
            $formulir->save();

            \Log::info(get_class(new CashAdvance()));



            if ($formulir->formulirable_type == get_class(new CashAdvance())) {
                $cashAdvance = CashAdvance::find($formulir->formulirable_id);
                $cashAdvance->remaining_amount = 0;
                $cashAdvance->save();
            }

            timeline_publish('close.formulir', trans('framework::framework/global.formulir.close.timeline', ['form_number' => $formulir->form_number]));
            
            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Close Form Success'
        );

        gritter_success(trans('framework::framework/global.formulir.close.success', ['form_number' => $formulir->form_number]), false);

        return $response;
    }

    /**
     * Open form
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function reopen()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(auth()->user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        $formulir_id = \Input::get('id');
        $formulir = Formulir::find($formulir_id);

        try {
            DB::beginTransaction();
            $formulir = Formulir::find($formulir_id);
            $formulir->form_status = 0;
            $formulir->save();

            timeline_publish('reopen.formulir', trans('framework::framework/global.formulir.reopen.timeline', ['form_number' => $formulir->form_number]));

            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Reopen Form Success'
        );

        gritter_success(trans('framework::framework/global.formulir.reopen.success', ['form_number' => $formulir->form_number]), false);

        return $response;
    }

    /**
     * Upload form file
     *
     * @param Request $request
     * @param $form
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, $form, $id)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $formulir = Formulir::find($id);
        $form_number = str_replace('/', '-', $formulir->form_number);
        $location = $form . '/' . $form_number . '/';
        $name = $request->file('file')->getClientOriginalName();

        if ($request->hasFile('file')) {
            StorageHelper::upload($request->file('file'), $location, $name);
        }
    }

    /**
     * Download form file
     *
     * @param $form
     * @param $id
     * @param $name
     * @return mixed
     */
    public function download($form, $id, $name)
    {
        $formulir = Formulir::find($id);
        $form_number = str_replace('/', '-', $formulir->form_number);
        $location = $form . '/' . $form_number . '/' . $name;
        return StorageHelper::download($location);
    }

    /**
     * Delete form file
     *
     * @param $form
     * @param $id
     * @param $name
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($form, $id, $name)
    {
        $formulir = Formulir::find($id);
        $form_number = str_replace('/', '-', $formulir->form_number);
        $location = $form . '/' . $form_number . '/' . $name;
        StorageHelper::delete($location);

        return redirect()->back();
    }

    /**
     * @param $formulir_id
     * @param $token
     *
     * @return $this
     * @throws \Point\Core\Exceptions\PointException
     */
    public function checkApprovalStatus($formulir_id, $token)
    {
        $formulir = Formulir::find($formulir_id);

        if (!$formulir) {
            throw new PointException('FORM NOT FOUND');
        }

        if ($formulir->request_approval_token != $token) {
            throw new PointException('TOKEN EXPIRED');
        }

        return view('framework::app.approval-status')->with('formulir', $formulir);
    }
}
