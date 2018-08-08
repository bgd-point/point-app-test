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
        // $subQuery = \DB::table('inventory')
        //                ->join('item as i', 'i.id', '=', 'inventory.item_id')
        //                ->select(
        //                    'item_id',
        //                    'warehouse_id',
        //                    'total_quantity',
        //                    'i.reminder_quantity_minimum'
        //                )
        //                ->selectRaw('CONCAT("[", i.code, "] ", i.name) as item_code')
        //                ->where('i.disabled', 0)
        //                ->where('i.reminder', 1)
        //                ->whereRaw('form_date = (
        //                     SELECT MAX(form_date)
        //                     FROM inventory ii
        //                     WHERE ii.item_id = inventory.item_id AND ii.warehouse_id = inventory.warehouse_id
        //                 )');
        // $list_inventory = \DB::table(\DB::raw('('.$subQuery->toSql().') as o1'))
        //             ->select('item_id', 'item_code', 'reminder_quantity_minimum')
        //             ->selectRaw('SUM(total_quantity) as total_quantity')
        //             ->having('total_quantity', '<=', 'reminder_quantity_minimum')
        //             ->groupBy('item_id')
        //             ->mergeBindings($subQuery)
        //             ->get();

        $items = Inventory::rightJoin('item', 'item.id', '=', 'inventory.item_id')
            ->where(\DB::raw('coalesce(inventory.total_quantity,0)'), '<', \DB::raw('item.reminder_quantity_minimum'))
            ->where('item.reminder', 1)
            ->where('item.disabled', 0)
            ->select(
                'item_id',
                'warehouse_id',
                'total_quantity',
                'item.reminder_quantity_minimum'
            )
            ->selectRaw('CONCAT("[", item.code, "] ", item.name) as item_code')
            ->orderBy('inventory.id', 'desc')
            ->groupBy('inventory.item_id')
            ->get();


        if ($merge_into_group && count($items) > 5) {
            array_push($array, [
                'url' => url('master/item/stock-reminder'),
                'deadline' => \Carbon::now(),
                'message' => 'You have some items that need restock. Please restock them.',
                'permission_slug' => 'create.point.purchasing.requisition'
            ]);
            return $array;
        }

        foreach ($items as $item) {
            $unit = Item::defaultUnit($item->item_id);
            $unit_name = $unit ? $unit->name : '';
            $minimum_qty = number_format_quantity($item->reminder_quantity_minimum);
            $current_qty = number_format_quantity($item->current_stock);
            array_push($array, [
                'url' => null,
                'deadline' => \Carbon::now(),
                'message' => 'Please restock this item. Minimum stock is <strong>' . $minimum_qty . ' ' . $unit_name . 
                             '</strong> but current stock is <strong>'. $current_qty . ' ' . $unit_name . '</strong>',
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
