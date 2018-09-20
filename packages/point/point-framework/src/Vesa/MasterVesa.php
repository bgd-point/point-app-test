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
        $items = Item::where('item.disabled', 0)->where('reminder', 1)->get();

        $date_from = $date_to = \Carbon::now();
        $stockReminder = [];

        foreach ($items as $item) {
            $unit = Item::defaultUnit($item->id);
            $unit_name = $unit ? $unit->name : '';
            $item_code = "[" . $item->code . "] " . $item->name;
            $minimum_qty = $item->reminder_quantity_minimum;
            $current_qty = inventory_get_closing_stock_all($date_from, $date_to, $item->id);

            if ($current_qty < $minimum_qty) {
                if ($merge_into_group && count($stockReminder) === 5) {
                    array_push($array, [
                        'url' => url('master/item/stock-reminder'),
                        'deadline' => \Carbon::now(),
                        'message' => 'You have some items that need restock. Please restock them.',
                        'permission_slug' => 'create.point.purchasing.requisition'
                    ]);
                    return $array;
                }

                array_push($stockReminder, [
                    'url' => url('purchasing/point/purchase-order/basic/create'),
                    'deadline' => \Carbon::now(),
                    'message' => 'Please restock <strong>'.$item_code.'</strong>. 
                              Minimum stock <strong>' . number_format_quantity($minimum_qty) . ' ' . $unit_name . 
                             '</strong>. Current stock <strong>'. number_format_quantity($current_qty) . ' ' . $unit_name . '</strong>.',
                    'permission_slug' => 'create.point.purchasing.requisition'
                ]);
            }
        }
        return array_merge($array, $stockReminder);
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
