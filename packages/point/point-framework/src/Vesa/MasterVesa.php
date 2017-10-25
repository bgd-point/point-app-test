<?php

namespace Point\Framework\Vesa;

use Point\Core\Models\User;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\SettingJournal;

class MasterVesa
{
    public static function getVesa()
    {
        $array = self::vesaCreateSettingJournal();
        $array = self::vesaStockReminder($array);
        $array = self::vesaSetUserWarehouse($array);

        return $array;
    }

    public static function getVesaCreateSettingJournal()
    {
        return self::vesaCreateSettingJournal([], false);
    }

    public static function getVesaStockReminder()
    {
        return self::vesaStockReminder([], false);
    }

    public static function getVesaSetUserWarehouse()
    {
        return self::vesaSetUserWarehouse([]);
    }

    private static function vesaCreateSettingJournal($array = [], $merge_into_group = true)
    {
        $list_setting_journal = SettingJournal::whereNull('coa_id');

        // Grouping vesa
        if ($merge_into_group && $list_setting_journal->count() > 5) {
            array_push($array, [
                'url' => url('master/coa/vesa-setting-journal'),
                'deadline' => null,
                'message' => 'Please setting journal',
                'permission_slug' => 'create.coa'
            ]);
            return $array;
        }

        // Push all
        foreach ($list_setting_journal->get() as $setting_journal) {
            array_push($array, [
                'url' => url('master/coa/setting-journal'),
                'deadline' => null,
                'message' => 'Please setting journal ' . $setting_journal->group . ' - ' . $setting_journal->name,
                'permission_slug' => 'create.coa'
            ]);
        }

        return $array;
    }

    private static function vesaStockReminder($array = [], $merge_into_group = true)
    {
        $list_inventory = Inventory::rightJoin('item', 'item.id', '=', 'inventory.item_id')
            ->where(\DB::raw('coalesce(inventory.total_quantity,0)'), '<', \DB::raw('item.reminder_quantity_minimum'))
            ->where('item.reminder', '1')
            ->select('item.*', 'inventory.total_quantity as total_quantity')
            ->orderBy('inventory.id', 'desc')
            ->groupBy('inventory.item_id')
            ->get();

        if ($merge_into_group && $list_inventory->count() > 5) {
            array_push($array, [
                'url' => url('master/item/stock-reminder'),
                'deadline' => null,
                'message' => 'Stock reminder below minimum quantity',
                'permission_slug' => 'create.point.purchasing.requisition'
            ]);
            return $array;
        }

        foreach ($list_inventory as $inventory) {
            array_push($array, [
                'url' => null,
                'deadline' => null,
                'message' => 'Stock reminder for [' . $inventory->code . '] ' . $inventory->name . ' is '
                    . number_format_quantity($inventory->total_quantity, 0)
                    . ' ' . Item::defaultUnit($inventory->id)->name
                    . ' < ' . number_format_quantity($inventory->reminder_quantity_minimum, 0)
                    . ' ' . Item::defaultUnit($inventory->id)->name,
                'permission_slug' => 'create.point.purchasing.requisition'
            ]);
        }

        return $array;
    }

    public static function vesaSetUserWarehouse($array = [])
    {
        if (User::where('users.id', '>', 1)->where('users.disabled', 0)->whereNotExists(function ($query) {
            $query->from('user_warehouse')->whereRaw('users.id = user_warehouse.user_id');
        })->count()) {
            array_push($array, [
                'url' => url('master/warehouse/set-user'),
                'deadline' => null,
                'message' => 'please set user warehouse',
                'permission_slug' => 'create.user'
            ]);
        }

        return $array;
    }
}
