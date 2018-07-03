<?php

namespace Point\Framework\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\PointFinance\Models\CashAdvance;
use Point\Core\Models\User;
use Point\Core\Helpers\QueueHelper;

class FormulirController extends Controller
{
    use ValidationTrait;

    /**
     * User pressed CANCEL button on the app
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
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
     * User pressed REQUEST CANCEL button on the app
     * User pressed CANCEL button but doesn't have enough previlege
     * App then will send email to ask for approval
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function requestCancel(Request $request) {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $formulir_id = \Input::get('formulir_id');
        $permission_slug = \Input::get('permission_slug');
        $formulir = Formulir::find($formulir_id);
        try {
            FormulirHelper::isAllowedToCancel($permission_slug, $formulir);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
        // code to send email
        $approver = User::find($formulir->approval_to);
        $token = md5(date('ymdhis'));

        $formulir->cancel_token = $token;
        $formulir->cancel_requested_at = \Carbon::now();
        $formulir->cancel_request_status = 0;
        $formulir->save();

        $data = array(
            'formulir' => $formulir,
            'token' => $token,
            'username' => auth()->user()->name,
            'url' => url('/'),
            'approver' => $approver,
        );
        $request = $request->input();

        \Queue::push(function ($job) use ($data, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send(
                'framework::email.cancel-formulir',
                $data,
                function ($message) use ($data) {
                    $message
                        ->to($data['approver']->email)
                        ->subject('Request approval form cancellation #' . $data['formulir']->form_number);
                }
            );
            $job->delete();
        });

        $response = array(
            'status' => 'success',
            'title' => 'Email sent',
            'msg' => 'You have sent email for deletion approval'
        );

        return $response;
    }

    /**
     * Admin approve cancellation form from email
     * @param $formulir_id
     * @param $token
     *
     * @return $this
     * @throws \Point\Core\Exceptions\PointException
     */
    public function cancelApproved($formulir_id, $token) {
        // code when admin approve the request
        // cancel the formulir without user auth check

        $formulir = Formulir::find($formulir_id);
        
        if (!$formulir) {
            throw new PointException('FORM NOT FOUND');
        }

        if ($formulir->cancel_token != $token) {
            throw new PointException('TOKEN EXPIRED');
        }
        
        DB::beginTransaction();

        try {
            FormulirHelper::cancelWithoutPermission($formulir_id);
            $formulir->cancel_request_status = 1;
            $formulir->save();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        DB::commit();

        return view('framework::app.approval-cancellation-status')->with('formulir', $formulir);
    }

    /**
     * Admin reject cancellation form from email
     * @param $formulir_id
     * @param $token
     *
     * @return $this
     * @throws \Point\Core\Exceptions\PointException
     */
    public function cancelRejected($formulir_id, $token)
    {
        $formulir = Formulir::find($formulir_id);

        if (!$formulir) {
            throw new PointException('FORM NOT FOUND');
        }

        if ($formulir->cancel_token != $token) {
            throw new PointException('TOKEN EXPIRED');
        }
        
        DB::beginTransaction();

        try {
            $formulir->cancel_token = "";
            $formulir->cancel_rejected_at = \Carbon::now();
            $formulir->cancel_request_status = -1;
            $formulir->save();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        DB::commit();

        return view('framework::app.approval-cancellation-status')->with('formulir', $formulir);
    }

    /**
     * @param $formulir_id
     * @param $token
     *
     * @return $this
     * @throws \Point\Core\Exceptions\PointException
     */
    public function checkCancelStatus($formulir_id, $token)
    {
        $formulir = Formulir::find($formulir_id);

        if (!$formulir) {
            throw new PointException('FORM NOT FOUND');
        }

        if ($formulir->cancel_token != $token) {
            throw new PointException('TOKEN EXPIRED');
        }

        return view('framework::app.approval-cancellation-status')->with('formulir', $formulir);
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
