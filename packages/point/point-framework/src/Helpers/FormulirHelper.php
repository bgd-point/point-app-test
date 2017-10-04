<?php

namespace Point\Framework\Helpers;

use Point\Core\Exceptions\PointException;
use Point\Core\Models\Setting;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\FormulirNumber;
use Point\PointExpedition\Models\ExpeditionOrderReference;
use Point\PointFinance\Models\PaymentReference;

class FormulirHelper
{

    /**
     * FORM STATUS HELPER
     * --------------------------------------------------------------------------------
     */

    /**
     * @param       $permission_slug
     * @param       $form_date
     * @param array $formulir_references
     *
     * @return bool|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public static function isAllowedToCreate($permission_slug, $form_date, $formulir_references = array())
    {
        // check permission
        if (!auth()->user()->may($permission_slug)) {
            throw new PointException('RESTRICTED PERMISSION ACCESS');
        }

        // check locking periode
        if (Setting::where('name', '=', 'lock-periode')->first()->value >= date('Y-m-d', strtotime($form_date))) {
            throw new PointException('RESTRICTED DATE ACCESS');
        }

        // do some check in the references
        for ($i = 0; $i < count($formulir_references); $i++) {
            $formulir = Formulir::find($formulir_references[$i]);

            // check if form referencen already closed
            if (self::isClose($formulir->id)) {
                throw new PointException('DUPLICATE FORM CREATE');
            }

            // check if formulir references not canceled
            if ($formulir->form_status == -1) {
                throw new PointException('RESTRICTED FORM ACCESS');
            }

            // check date not lower than references
            if ($form_date < date('Y-m-d', strtotime($formulir->form_date))) {
                throw new PointException('RESTRICTED ACCESS - FORM DATE MUST HIGHER THAN ' . date_format_view($formulir->form_date));
            }

            // check date not lower than reference date
            if ($form_date <= Setting::where('name', '=', 'lock-periode')->first()->value) {
                throw new PointException('RESTRICTED ACCESS - FORM DATE MUST HIGHER THAN ' . date_format_view(Setting::where('name', '=', 'lock-periode')->first()->value));
            }
        }
    }

    /**
     * @param $formulir_id
     *
     * @return bool
     */
    public static function isClose($formulir_id)
    {
        if (Formulir::where('id', '=', $formulir_id)->where('form_status', '=', 1)->get()->count()) {
            return true;
        }
        return false;
    }

    /**
     * @param $permission_slug
     * @param $form_date
     * @param $formulir
     *
     * @return bool|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public static function isAllowedToUpdate($permission_slug, $form_date, $formulir)
    {
        // check permission
        if (!auth()->user()->may($permission_slug)) {
            throw new PointException('RESTRICTED PERMISSION ACCESS');
        }

        // check locking periode
        if (Setting::where('name', '=', 'lock-periode')->first()->value >= date('Y-m-d', strtotime($form_date))) {
            throw new PointException('RESTRICTED DATE ACCESS');
        }

        // check if formulir not locked
        if (self::isLocked($formulir->id)) {
            throw new PointException('RESTRICTED LOCKED ACCESS');
        }
    }

    /**
     * Check if from locked or not. locked form cannot do delete or edit method
     *
     * @param $locked_type
     * @param $locked_id
     *
     * @return bool
     */
    public static function isLocked($locked_id)
    {
        if (FormulirLock::where('locked_id', '=', $locked_id)->where('locked', '=', true)->get()->count()) {
            return true;
        }

        return false;
    }

    /**
     * @param $formulir_id
     *
     * @return bool
     */
    public static function isCanceled($formulir_id)
    {
        if (Formulir::where('id', '=', $formulir_id)->where('form_status', '=', -1)->get()->count()) {
            return true;
        }
        return false;
    }

    public static function isOpen($formulir_id)
    {
        if (Formulir::where('id', '=', $formulir_id)->where('form_status', '=', 0)->get()->count()) {
            return true;
        }

        return false;
    }

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    public static function viewClose($formulir, $permission_slug)
    {
        // form status not pending
        if ($formulir->form_status != 0) {
            return false;
        }

        // user doesn't have a permission
        if (!auth()->user()->may($permission_slug)) {
            return false;
        }

        return true;
    }

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    public static function viewReopen($formulir, $permission_slug)
    {
        // form status not pending
        if ($formulir->form_status != 1) {
            return false;
        }

        // user doesn't have a permission
        if (!auth()->user()->may($permission_slug)) {
            return false;
        }

        return true;
    }

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    public static function viewEdit($formulir, $permission_slug)
    {
        // formulir locked
        if (self::isLocked($formulir->id)) {
            return false;
        }

        // user doesn't have a permission
        if (!auth()->user()->may($permission_slug)) {
            return false;
        }
        return true;
    }

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    public static function viewEmailVendor($formulir, $permission_slug)
    {
        if ($formulir->approval_status != 1) {
            return false;
        }

        if ($formulir->form_status == -1) {
            return false;
        }

        // user doesn't have a permission
        if (!auth()->user()->may($permission_slug)) {
            return false;
        }

        return true;
    }

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    public static function viewCancel($formulir, $permission_slug)
    {
        // form status is not canceled
        if ($formulir->form_status == -1) {
            return false;
        }

        // formulir locked
        if (self::isLocked($formulir->id)) {
            return false;
        }

        // user doesn't have a permission
        if (!auth()->user()->may($permission_slug)) {
            return false;
        }

        return true;
    }

    /**
     * FORM APPROVAL HELPER
     * --------------------------------------------------------------------------------
     */

    /**
     * Check is user allowed to view approval button
     *
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    public static function viewApproval($formulir, $permission_slug)
    {
        // approval status not pending
        if ($formulir->approval_status != 0) {
            return false;
        }

        // form status not pending
        if ($formulir->form_status == -1) {
            return false;
        }

        // user doesn't have a permission
        if (!auth()->user()->may($permission_slug)) {
            return false;
        }

        // user should match with approval to request
        if (auth()->user()->id != $formulir->approval_to) {
            return false;
        }

        return true;
    }

    /**
     * Check is form approved or not
     *
     * @param $formulir_id
     *
     * @return bool
     */
    public static function isApproved($formulir_id)
    {
        if (Formulir::where('id', '=', $formulir_id)->where('approval_status', '=', 1)->get()->count()) {
            return true;
        }

        return false;
    }

    /**
     * Check is form rejected or not
     *
     * @param $formulir_id
     *
     * @return bool
     */
    public static function isRejected($formulir_id)
    {
        if (Formulir::where('id', '=', $formulir_id)->where('approval_status', '=', -1)->get()->count()) {
            return true;
        }

        return false;
    }

    /**
     * Approve form
     *
     * @param $formulir
     * @param $approval_message
     * @param $permission_slug
     * @param $token
     */
    public static function approve($formulir, $approval_message, $permission_slug, $token)
    {
        // check if formulir exist
        if (!$formulir) {
            throw new PointException('FORM NOT FOUND');
        }

        if ($formulir->approval_status != 0) {
            throw new PointException('RESTRICTED ACCESS, FORM ALREADY APPROVED / REJECTED');
        }

        if ($formulir->form_number == null) {
            throw new PointException('RESTRICTED ACCESS, FORM ALREADY EDITED');
        }

        if (auth()->check()) {
            // if the user have permission access to approval
            if (!auth()->user()->may($permission_slug)) {
                throw new PointException('UNAUTHORIZED USER');
            }
        } else {
            // token not match
            if ($formulir->request_approval_token != $token) {
                throw new PointException('TOKEN EXPIRED');
            }
        }

        $formulir->approval_status = 1;
        $formulir->approval_message = $approval_message;
        $formulir->approval_at = date('Y-m-d H:i:s');
        $formulir->save();
    }

    /**
     * Reject form
     *
     * @param $formulir
     * @param $approval_message
     * @param $permission_slug
     * @param $token
     */
    public static function reject($formulir, $approval_message, $permission_slug, $token)
    {
        // check if formulir exist
        if (!$formulir) {
            throw new PointException('FORM NOT FOUND');
        }

        if ($formulir->approval_status != 0) {
            throw new PointException('RESTRICTED ACCESS');
        }

        if (auth()->check()) {
            // if the user have permission access to approval
            if (!auth()->user()->may($permission_slug)) {
                throw new PointException('UNAUTHORIZED USER');
            }
        } else {
            // token not match
            if ($formulir->request_approval_token != $token) {
                throw new PointException('TOKEN EXPIRED');
            }
        }

        $formulir->approval_status = -1;
        $formulir->approval_message = $approval_message;
        $formulir->approval_at = date('Y-m-d H:i:s');
        $formulir->save();
    }

    /**
     * FORM LOCK HELPER
     * --------------------------------------------------------------------------------
     */

    /**
     * @param $locked_id
     *
     * @return bool
     */
    public static function isNotLocked($locked_id)
    {
        if (FormulirLock::where('locked_id', '=', $locked_id)->where('locked', '=', true)->get()->count()) {
            return false;
        }

        return true;
    }

    /**
     * Lock form
     *
     * @param $locked_id
     * @param $formulir_id
     */
    public static function lock($locked_id, $locking_id)
    {
        $formulir_lock = new FormulirLock;
        $formulir_lock->locked_id = $locked_id;
        $formulir_lock->locking_id = $locking_id;
        $formulir_lock->locked = true;
        $formulir_lock->save();
    }

    /**
     * @param $locking_id
     *
     * @return mixed
     */
    public static function getLocked($locking_id)
    {
        $formulir_lock = FormulirLock::where('locking_id', '=', $locking_id)->first();
        if (! $formulir_lock) {
            return null;
        }
        return Formulir::find($formulir_lock->locked_id);
    }

    /**
     * @param $locking_id
     *
     * @return mixed
     */
    public static function getLockedModel($locking_id)
    {
        $formulir_lock = FormulirLock::where('locking_id', '=', $locking_id)->first();
        $formulir = Formulir::find($formulir_lock->locked_id);
        $model = $formulir->formulirable_type;
        return $model::find($formulir->formulirable_id);
    }

    /**
     * @param $locking_id
     *
     * @return array
     */
    public static function getLockedModelIds($locking_id)
    {
        $formulir_locks = FormulirLock::where('locking_id', '=', $locking_id)->get();
        $array = [];
        foreach ($formulir_locks as $formulir_lock) {
            $formulir = Formulir::find($formulir_lock->locked_id);
            array_push($array, $formulir->formulirable_id);
        }

        return $array;
    }

    /**
     * Update formulir polymorphic relation
     *
     * @param $formulir
     * @param $formulirable_type
     * @param $formulirable_id
     */
    public static function updateFormulirable($formulir, $formulirable_type, $formulirable_id)
    {
        $formulir->formulirable_type = $formulirable_type;
        $formulir->formulirable_id = $formulirable_id;
        $formulir->save();
    }

    /**
     * Update token from send email request approval
     *
     * @param $formulir
     * @param $token
     */
    public static function updateToken($formulir, $token)
    {
        $formulir->request_approval_at = \Carbon::now();
        $formulir->request_approval_token = $token;
        $formulir->save();
    }

    /**
     * @param $request
     * @param $formulir_number_code
     * @param $user_id
     *
     * @return \Point\Framework\Models\Formulir
     */
    public static function create($request, $formulir_number_code)
    {
        $form_date = date_format_db($request['form_date'], array_key_exists('time', $request) ? $request['time'] : 'original');
        $form_number = FormulirHelper::number($formulir_number_code, $form_date);

        $formulir = new Formulir;
        $formulir->form_date = $form_date;
        $formulir->form_number = $form_number['form_number'];
        $formulir->form_raw_number = $form_number['raw'];
        $formulir->notes = array_key_exists('notes', $request) ? $request['notes'] : '';
        $formulir->approval_to = array_key_exists('approval_to', $request) ? $request['approval_to'] : 1;
        $formulir->approval_status = array_key_exists('approval_to', $request) ? 0 : 1;
        $formulir->approval_message = '';
        $formulir->created_by = $request['user']->id;
        $formulir->updated_by = $request['user']->id;
        if (!$formulir->save()) {
            gritter_error('create has been failed', false);
        }

        return $formulir;
    }

    /**
     * FORM NUMBER HELPER
     * --------------------------------------------------------------------------------
     */

    /**
     * Generate number form
     *
     * @param $form_name
     * @param $date
     *
     * @return string
     */
    public static function number($form_name, $date)
    {
        // EX : PR/UM/0001/XII/15
        //      {CODE}/INCREMENT/MONTH/YEAR
        $code = 'UNKNOWN/';
        $time = '/' . self::numberToRoman(date('m', strtotime($date))) . '/' . date('y', strtotime($date));
        $formulir_number = FormulirNumber::where('name', '=', $form_name)->first();
        if ($formulir_number) {
            $code = $formulir_number->code;
        }

        $formulir = Formulir::where('form_number', 'like', $code . '%' . $time)
            ->orderBy('form_raw_number', 'desc')
            ->first();

        $num = 0;

        if ($formulir) {
            $num = $formulir->form_raw_number;
        }

        $num += 1;

        $raw_num = $num;
        $formatted_num = sprintf("%04d", $num);

        return [
            'raw' => $raw_num,
            'form_number' => $code . $formatted_num . $time
        ];
    }

    /**
     * Roman converter
     *
     * @param $integer
     *
     * @return string
     */
    private static function numberToRoman($integer)
    {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * @param $request
     * @param $form_number
     * @param $user_id
     *
     * @return \Point\Framework\Models\Formulir
     */
    public static function update($request, $form_number, $form_raw_number)
    {
        $formulir = new Formulir;
        $formulir->form_date = date_format_db($request['form_date'], array_key_exists('time', $request) ? $request['time'] : 'original');
        $formulir->form_number = $form_number;
        $formulir->form_raw_number = $form_raw_number;
        $formulir->notes = array_key_exists('notes', $request) ? $request['notes'] : '';
        $formulir->approval_to = array_key_exists('approval_to', $request) ? $request['approval_to'] : 1;
        $formulir->approval_status = array_key_exists('approval_to', $request) ? 0 : 1;
        $formulir->approval_message = '';
        $formulir->created_by = $request['user']->id;
        $formulir->updated_by = $request['user']->id;
        if (!$formulir->save()) {
            gritter_error('create has been failed', false);
        }

        return $formulir;
    }

    /**
     * @param $formulir_id
     * @param $notes
     */
    public static function archive($request, $formulir_id)
    {
        $formulir_old = Formulir::find($formulir_id);
        $formulir_old->archived = $formulir_old->form_number;
        $formulir_old->form_number = null;
        $formulir_old->edit_notes = array_key_exists('edit_notes', $request) ? $request['edit_notes'] : '';
        $formulir_old->updated_by = $request['user']->id;
        if (!$formulir_old->save()) {
            gritter_error('create has been failed', false);
        }

        self::clearRelation($formulir_old);

        return $formulir_old;
    }

    public static function clearRelation($formulir)
    {
        self::unlock($formulir->id);
        ReferHelper::cancel($formulir->formulirable_type, $formulir->formulirable_id);
        InventoryHelper::remove($formulir->id);
        JournalHelper::remove($formulir->id);
        AccountPayableAndReceivableHelper::remove($formulir->id);
        AllocationHelper::remove($formulir->id);
        self::cancelPaymentReference($formulir->id);
        self::cancelExpeditionReference($formulir->id);
    }

    /**
     * Unlock form
     *
     * @param $formulir_id
     */
    public static function unlock($locking_id)
    {
        $list_formulir_lock = FormulirLock::where('locking_id', '=', $locking_id)->get();

        foreach ($list_formulir_lock as $formulir_lock) {
            $locked_form = Formulir::find($formulir_lock->locked_id);
            $locked_form->form_status = 0;
            $locked_form->save();

            $formulir_lock->locked = false;
            $formulir_lock->save();
        }
    }

    /**
     * @param $formulir_id
     */
    public static function close($formulir_id)
    {
        $formulir = Formulir::find($formulir_id);
        $formulir->form_status = 1;
        $formulir->save();
    }

    /**
     * @param $permission_slug
     * @param $formulir_id
     */
    public static function cancel($permission_slug, $formulir_id)
    {
        $formulir = Formulir::find($formulir_id);

        self::isAllowedToCancel($permission_slug, $formulir);

        $formulir->form_status = -1;
        $formulir->canceled_at = date('Y-m-d H:i:s');
        $formulir->canceled_by = auth()->user()->id;
        $formulir->save();

        self::clearRelation($formulir);

        timeline_publish('cancel form', 'cancel form ' . $formulir->form_number . ' success');
    }

    public static function cancelPaymentReference($payment_reference_id)
    {
        $payment_reference = PaymentReference::where('payment_reference_id', '=', $payment_reference_id)->first();
        if ($payment_reference) {
            $payment_reference->delete();
        }
    }

    public static function cancelExpeditionReference($expedition_reference_id)
    {
        $payment_reference = ExpeditionOrderReference::where('expedition_reference_id', '=', $expedition_reference_id)->first();
        if ($payment_reference) {
            $payment_reference->delete();
        }
    }

    /**
     * @param $permission_slug
     * @param $formulir
     *
     * @return bool|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public static function isAllowedToCancel($permission_slug, $formulir)
    {
        if (!auth()->user()->may($permission_slug)) {
            throw new PointException('RESTRICTED ACCESS');
        }

        // check locking periode
        if (Setting::where('name', '=', 'lock-periode')->first()->value >= $formulir->form_date) {
            throw new PointException('RESTRICTED ACCESS');
        }

        // check if formulir not canceled
        if ($formulir->form_status == -1) {
            throw new PointException('RESTRICTED ACCESS');
        }

        // check locked reference form
        if (self::isLocked($formulir->id)) {
            throw new PointException('RESTRICTED ACCESS');
        }
    }
}
