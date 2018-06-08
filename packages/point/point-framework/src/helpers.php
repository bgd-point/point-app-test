<?php

use Point\Framework\Helpers\AccessHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Helpers\ClientHelper;

if (! function_exists('access_is_allowed')) {
    function access_is_allowed($permission_slug)
    {
        return AccessHelper::isAllowed($permission_slug);
    }
}

if (! function_exists('access_is_allowed_to_view')) {
    function access_is_allowed_to_view($permission_slug)
    {
        return AccessHelper::isAllowedToView($permission_slug);
    }
}

if (! function_exists('formulir_url')) {
    function formulir_url($formulir)
    {
        return FormulirHelper::formulirUrl($formulir);
    }
}

if (! function_exists('formulir_is_allowed_to_create')) {
    function formulir_is_allowed_to_create($permission_slug, $form_date, $formulir_references)
    {
        return FormulirHelper::isAllowedToCreate($permission_slug, $form_date, $formulir_references);
    }
}

if (! function_exists('formulir_is_allowed_to_update')) {
    function formulir_is_allowed_to_update($permission_slug, $form_date, $formulir)
    {
        return FormulirHelper::isAllowedToUpdate($permission_slug, $form_date, $formulir);
    }
}

if (! function_exists('formulir_is_allowed_to_cancel')) {
    function formulir_is_allowed_to_cancel($permission_slug, $formulir)
    {
        return FormulirHelper::isAllowedToCancel($permission_slug, $formulir);
    }
}

if (! function_exists('formulir_view_approval')) {

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    function formulir_view_approval($formulir, $permission_slug)
    {
        return FormulirHelper::viewApproval($formulir, $permission_slug);
    }
}

if (! function_exists('formulir_view_edit')) {

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    function formulir_view_edit($formulir, $permission_slug)
    {
        return FormulirHelper::viewEdit($formulir, $permission_slug);
    }
}

if (! function_exists('formulir_view_cancel')) {

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    function formulir_view_cancel($formulir, $permission_slug)
    {
        return FormulirHelper::viewCancel($formulir, $permission_slug);
    }
}

if (! function_exists('formulir_view_cancel_or_request_cancel')) {

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    function formulir_view_cancel_or_request_cancel($formulir, $permission_slug_delete, $permission_slug_approve)
    {
        return FormulirHelper::viewCancelOrRequestCancel($formulir, $permission_slug_delete, $permission_slug_approve);
    }
}

if (! function_exists('formulir_view_email_vendor')) {

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    function formulir_view_email_vendor($formulir, $permission_slug)
    {
        return FormulirHelper::viewEmailVendor($formulir, $permission_slug);
    }
}

if (! function_exists('formulir_view_close')) {

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    function formulir_view_close($formulir, $permission_slug)
    {
        return FormulirHelper::viewClose($formulir, $permission_slug);
    }
}

if (! function_exists('formulir_view_reopen')) {

    /**
     * @param $formulir
     * @param $permission_slug
     *
     * @return bool
     */
    function formulir_view_reopen($formulir, $permission_slug)
    {
        return FormulirHelper::viewReopen($formulir, $permission_slug);
    }
}

if (! function_exists('formulir_is_locked')) {

    /**
     * @param $locked_id
     *
     * @return bool
     */
    function formulir_is_locked($locked_id)
    {
        return FormulirHelper::isLocked($locked_id);
    }
}

if (! function_exists('formulir_is_not_locked')) {

    /**
     * @param $locked_id
     *
     * @return bool
     */
    function formulir_is_not_locked($locked_id)
    {
        return FormulirHelper::isNotLocked($locked_id);
    }
}

if (! function_exists('formulir_lock')) {

    /**
     * @param $locked_id
     * @param $locking_id
     */
    function formulir_lock($locked_id, $locking_id)
    {
        return FormulirHelper::lock($locked_id, $locking_id);
    }
}

if (! function_exists('formulir_unlock')) {

    /**
     * @param $locking_id
     */
    function formulir_unlock($locking_id)
    {
        return FormulirHelper::unlock($locking_id);
    }
}

if (! function_exists('formulir_get_locked')) {

    /**
     * @param $locked_id
     * @param $locking_id
     */
    function formulir_get_locked($locking_id)
    {
        return FormulirHelper::getLocked($locking_id);
    }
}

if (! function_exists('formulir_number')) {

    /**
     * @param $form_name
     * @param $date
     *
     * @return string
     */
    function formulir_number($form_name, $date)
    {
        return FormulirHelper::number($form_name, $date);
    }
}

if (! function_exists('formulir_update_token')) {

    /**
     * @param $request
     * @param $formulir_number_code
     *
     * @return \Point\Framework\Models\Formulir
     */
    function formulir_update_token($formulir, $token)
    {
        return FormulirHelper::updateToken($formulir, $token);
    }
}

if (! function_exists('formulir_create')) {

    /**
     * @param $request
     * @param $formulir_number_code
     *
     * @return \Point\Framework\Models\Formulir
     */
    function formulir_create($request, $formulir_number_code)
    {
        return FormulirHelper::create($request, $formulir_number_code);
    }
}

if (! function_exists('formulir_archive')) {

    /**
     * @param $formulir_id
     * @param $notes
     */
    function formulir_archive($request, $formulir_id)
    {
        return FormulirHelper::archive($request, $formulir_id);
    }
}

if (! function_exists('formulir_is_open')) {

    /**
     * @param $formulir_id
     * @param $notes
     */
    function formulir_is_open($formulir_id)
    {
        return FormulirHelper::isOpen($formulir_id);
    }
}

if (! function_exists('formulir_is_close')) {

    /**
     * @param $formulir_id
     * @param $notes
     */
    function formulir_is_close($formulir_id)
    {
        return FormulirHelper::isClose($formulir_id);
    }
}

/**
 * Person Helper global function
 */

if (! function_exists('person_get_by_type')) {

    /**
     * @param $person_type_slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function person_get_by_type($person_type_slug)
    {
        return PersonHelper::getByType($person_type_slug);
    }
}

if (! function_exists('person_get_code')) {

    /**
     * @param $person_type
     * @return string|void
     */
    function person_get_code($person_type)
    {
        return PersonHelper::getCode($person_type);
    }
}

if (! function_exists('person_get_type')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function person_get_type($person_type_slug)
    {
        return PersonHelper::getType($person_type_slug);
    }
}

if (! function_exists('client_has_addon')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function client_has_addon($code)
    {
        return ClientHelper::hasAddon($code);
    }
}

if (! function_exists('inventory_get_opening_stock')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_opening_stock($date_from, $item_id, $warehouse_id)
    {
        return InventoryHelper::getOpeningStock($date_from, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_opening_stock_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_opening_stock_all($date_from, $item_id)
    {
        return InventoryHelper::getOpeningStockAll($date_from, $item_id);
    }
}

if (! function_exists('inventory_get_opening_value')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_opening_value($date_from, $item_id, $warehouse_id)
    {
        return InventoryHelper::getOpeningValue($date_from, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_opening_value_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_opening_value_all($date_from, $item_id)
    {
        return InventoryHelper::getOpeningValueAll($date_from, $item_id);
    }
}

if (! function_exists('get_url_person')) {
    /**
     * Get link to person
     * @param $id
     */
    function get_url_person($id)
    {
        return PersonHelper::getUrl($id);
    }
}

if (! function_exists('inventory_get_stock_in')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_stock_in($date_from, $date_to, $item_id, $warehouse_id)
    {
        return InventoryHelper::getStockIn($date_from, $date_to, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_stock_in_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_stock_in_all($date_from, $date_to, $item_id)
    {
        return InventoryHelper::getStockInAll($date_from, $date_to, $item_id);
    }
}

if (! function_exists('inventory_get_value_in')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_value_in($date_from, $date_to, $item_id, $warehouse_id)
    {
        return InventoryHelper::getValueIn($date_from, $date_to, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_value_in_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_value_in_all($date_from, $date_to, $item_id)
    {
        return InventoryHelper::getValueInAll($date_from, $date_to, $item_id);
    }
}

if (! function_exists('inventory_get_stock_out')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_stock_out($date_from, $date_to, $item_id, $warehouse_id)
    {
        return InventoryHelper::getStockOut($date_from, $date_to, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_stock_out_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_stock_out_all($date_from, $date_to, $item_id)
    {
        return InventoryHelper::getStockOutAll($date_from, $date_to, $item_id);
    }
}

if (! function_exists('inventory_get_value_out')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_value_out($date_from, $date_to, $item_id, $warehouse_id)
    {
        return InventoryHelper::getValueOut($date_from, $date_to, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_value_out_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_value_out_all($date_from, $date_to, $item_id)
    {
        return InventoryHelper::getValueOutAll($date_from, $date_to, $item_id);
    }
}

if (! function_exists('inventory_get_closing_stock')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_closing_stock($date_from, $date_to, $item_id, $warehouse_id)
    {
        return InventoryHelper::getClosingStock($date_from, $date_to, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_closing_stock_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_closing_stock_all($date_from, $date_to, $item_id)
    {
        return InventoryHelper::getClosingStockAll($date_from, $date_to, $item_id);
    }
}

if (! function_exists('inventory_get_closing_value')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_closing_value($date_from, $date_to, $item_id, $warehouse_id)
    {
        return InventoryHelper::getClosingValue($date_from, $date_to, $item_id, $warehouse_id);
    }
}

if (! function_exists('inventory_get_closing_value_all')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_closing_value_all($date_from, $date_to, $item_id)
    {
        return InventoryHelper::getClosingValueAll($date_from, $date_to, $item_id);
    }
}

if (! function_exists('inventory_get_available_stock')) {

    /**
     * @param $person_type_slug
     * @return string|void
     */
    function inventory_get_available_stock($date, $item_id, $warehouse_id)
    {
        return InventoryHelper::getAvailableStock($date, $item_id, $warehouse_id);
    }
}
