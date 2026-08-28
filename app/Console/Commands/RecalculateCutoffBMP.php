<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockCorrection\StockCorrectionItem;
use Point\PointInventory\Helpers\StockCorrectionHelper;

class RecalculateCutoffBMP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:cutoff-bmp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'recalculate inventory';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('handle inventory all');

        $json = '[
  {
    "code": "BMPP0005",
    "value": 0
  },
  {
    "code": "BMPP0006",
    "value": 0
  },
  {
    "code": "BMPP0639",
    "value": 0
  },
  {
    "code": "BMPP0037",
    "value": "2,302,000.00"
  },
  {
    "code": "BMPP0655",
    "value": "2,732,000.00"
  },
  {
    "code": "BMPP0008",
    "value": 0
  },
  {
    "code": "BMPP0009",
    "value": "61,331.26"
  },
  {
    "code": "BMPP0012",
    "value": 0
  },
  {
    "code": "BMPP0013",
    "value": 0
  },
  {
    "code": "BMPP0015",
    "value": 0
  },
  {
    "code": "BMPP0016",
    "value": 0
  },
  {
    "code": "BMPP0188",
    "value": 0
  },
  {
    "code": "BMPP0189",
    "value": 0
  },
  {
    "code": "BMPP0693",
    "value": 0
  },
  {
    "code": "BMPP0339",
    "value": 0
  },
  {
    "code": "BMPP0337",
    "value": "15,333.33"
  },
  {
    "code": "BMPP0653",
    "value": 0
  },
  {
    "code": "BMPP0694",
    "value": "77,387.00"
  },
  {
    "code": "BMPP0322",
    "value": 0
  },
  {
    "code": "BMPP0695",
    "value": 0
  },
  {
    "code": "BMPP0696",
    "value": "22,000.00"
  },
  {
    "code": "BMPP0729",
    "value": 0
  },
  {
    "code": "BMPP0697",
    "value": "17,298.00"
  },
  {
    "code": "BMPP0115",
    "value": "283,563.00"
  },
  {
    "code": "BMPP0119",
    "value": "145,725.00"
  },
  {
    "code": "BMPP0114",
    "value": "171,149.00"
  },
  {
    "code": "BMPP0118",
    "value": 0
  },
  {
    "code": "BMPP0113",
    "value": "83,392.00"
  },
  {
    "code": "BMPP0117",
    "value": "51,689.00"
  },
  {
    "code": "BMPP0112",
    "value": "56,819.80"
  },
  {
    "code": "BMPP0110",
    "value": "38,059.00"
  },
  {
    "code": "BMPP0109",
    "value": "25,346.00"
  },
  {
    "code": "BMPP0108",
    "value": "18,712.44"
  },
  {
    "code": "BMPP0698",
    "value": "72,000.00"
  },
  {
    "code": "BMPP0699",
    "value": "47,000.00"
  },
  {
    "code": "BMPP0129",
    "value": "16,300.00"
  },
  {
    "code": "BMPP0128",
    "value": "5,360.00"
  },
  {
    "code": "BMPP0122",
    "value": "3,293.00"
  },
  {
    "code": "BMPP0123",
    "value": "7,406.05"
  },
  {
    "code": "BMPP0121",
    "value": "3,599.00"
  },
  {
    "code": "BMPP0399",
    "value": "1,914.00"
  },
  {
    "code": "BMPP0130",
    "value": "24,734.00"
  },
  {
    "code": "BMPP0563",
    "value": "20,599.00"
  },
  {
    "code": "BMPP0700",
    "value": "7,500.00"
  },
  {
    "code": "BMPP0701",
    "value": "6,000.00"
  },
  {
    "code": "BMPP0657",
    "value": "62,400.00"
  },
  {
    "code": "BMPP0702",
    "value": "61,300.00"
  },
  {
    "code": "BMPP0479",
    "value": "53,600.00"
  },
  {
    "code": "BMPP0732",
    "value": "60,300.00"
  },
  {
    "code": "BMPP0733",
    "value": 0
  },
  {
    "code": "BMPP0529",
    "value": 0
  },
  {
    "code": "BMPP0141",
    "value": "60,300.00"
  },
  {
    "code": "BMPP0659",
    "value": "8,653.25"
  },
  {
    "code": "BMPP0537",
    "value": "6,968.00"
  },
  {
    "code": "BMPP0538",
    "value": 0
  },
  {
    "code": "BMPP0703",
    "value": "9,000.00"
  },
  {
    "code": "BMPP0477",
    "value": 0
  },
  {
    "code": "BMPP0551",
    "value": "12,000.00"
  },
  {
    "code": "BMPP0552",
    "value": "11,000.00"
  },
  {
    "code": "BMPP0565",
    "value": "3,062.00"
  },
  {
    "code": "BMPP266",
    "value": "61,357.60"
  },
  {
    "code": "BMPP0491",
    "value": "9,009.00"
  },
  {
    "code": "BMPP0180",
    "value": 856
  },
  {
    "code": "BMPP0492",
    "value": "1,216,216.00"
  },
  {
    "code": "BMPP0096",
    "value": "12,090.00"
  },
  {
    "code": "BMPP0640",
    "value": 0
  },
  {
    "code": "BMPP0641",
    "value": "35,000.00"
  },
  {
    "code": "BMPP0651",
    "value": 0
  },
  {
    "code": "BMPP0340",
    "value": 0
  },
  {
    "code": "BMPP0744",
    "value": 0
  },
  {
    "code": "BMPP0436",
    "value": "2,500,000.00"
  },
  {
    "code": "BMPP0067",
    "value": "12,492.80"
  },
  {
    "code": "BMPP0704",
    "value": "21,895.50"
  },
  {
    "code": "BMPP0705",
    "value": "11,565.77"
  },
  {
    "code": "BMPP0605",
    "value": "285,888.70"
  },
  {
    "code": "BMPP0643",
    "value": "441,441.45"
  },
  {
    "code": "BMPP0604",
    "value": "59,972.38"
  },
  {
    "code": "BMPP0644",
    "value": "66,666.67"
  },
  {
    "code": "BMPP0645",
    "value": "274,774.80"
  },
  {
    "code": "BMPP0598",
    "value": "194,462.64"
  },
  {
    "code": "BMPP0599",
    "value": "49,057.37"
  },
  {
    "code": "BMPP0602",
    "value": "5,343.23"
  },
  {
    "code": "BMPP0646",
    "value": "128,828.83"
  },
  {
    "code": "BMPP0647",
    "value": "136,621.29"
  },
  {
    "code": "BMPP0601",
    "value": "47,567.57"
  },
  {
    "code": "BMPP0648",
    "value": "45,045.05"
  },
  {
    "code": "BMPP0603",
    "value": "64,864.86"
  },
  {
    "code": "BMPP0649",
    "value": "52,252.25"
  },
  {
    "code": "BMPP0072",
    "value": "21,000.00"
  },
  {
    "code": "BMPP0706",
    "value": "13,000.00"
  },
  {
    "code": "BMPP0707",
    "value": "1,643,304.00"
  },
  {
    "code": "BMPP0246",
    "value": "37,000.00"
  },
  {
    "code": "BMPP0463",
    "value": "1,945,920.00"
  },
  {
    "code": "BMPP0034",
    "value": "375,000.00"
  },
  {
    "code": "BMPP0440",
    "value": "58,558.50"
  },
  {
    "code": "BMPP0734",
    "value": "120,000.00"
  },
  {
    "code": "BMPP0735",
    "value": "173,000.00"
  },
  {
    "code": "BMPP0467",
    "value": "207,207.21"
  },
  {
    "code": "BMPP0036",
    "value": "42,000.00"
  },
  {
    "code": "BMPP0642",
    "value": "77,387.38"
  },
  {
    "code": "BMPP0051",
    "value": "102,702.70"
  },
  {
    "code": "BMPP0736",
    "value": "16,000.00"
  },
  {
    "code": "BMPP0124",
    "value": "100,000.00"
  },
  {
    "code": "BMPP0683",
    "value": "40,540.50"
  },
  {
    "code": "BMPP0656",
    "value": "65,765.77"
  },
  {
    "code": "BMPP0730",
    "value": "48,648.67"
  },
  {
    "code": "BMPP0459",
    "value": "20,000.00"
  },
  {
    "code": "BMPP0453",
    "value": "33,333.33"
  },
  {
    "code": "BMPP0708",
    "value": "25,000.00"
  },
  {
    "code": "BMPP0454",
    "value": "18,018.02"
  },
  {
    "code": "BMPP0709",
    "value": "300,000.00"
  },
  {
    "code": "BMPP0710",
    "value": "13,400.00"
  },
  {
    "code": "BMPP0670",
    "value": "35,000.00"
  },
  {
    "code": "BMPP0711",
    "value": "23,000.00"
  },
  {
    "code": "BMPP0452",
    "value": "250,000.00"
  },
  {
    "code": "BMPP0462",
    "value": "32,000.00"
  },
  {
    "code": "BMPP0461",
    "value": "85,000.00"
  },
  {
    "code": "BMPP0353",
    "value": "7,500.00"
  },
  {
    "code": "BMPP0712",
    "value": "35,518.39"
  },
  {
    "code": "BMPP0504",
    "value": "81,081.00"
  },
  {
    "code": "BMPP0505",
    "value": "81,081.00"
  },
  {
    "code": "BMPP0324",
    "value": "27,027.03"
  },
  {
    "code": "BMPP0298",
    "value": "42,342.34"
  },
  {
    "code": "BMPP0297",
    "value": "65,765.77"
  },
  {
    "code": "BMPP0498",
    "value": "216,216.22"
  },
  {
    "code": "BMPP0306",
    "value": "346,846.85"
  },
  {
    "code": "BMPP0713",
    "value": "5,000.00"
  },
  {
    "code": "BMPP0486",
    "value": "1,056,756.72"
  },
  {
    "code": "BMPP0111",
    "value": "65,243.15"
  },
  {
    "code": "BMPP0731",
    "value": "111,495.50"
  },
  {
    "code": "BMPP0688",
    "value": "12,941.40"
  },
  {
    "code": "BMPP0714",
    "value": "3,292.80"
  },
  {
    "code": "BMPP0673",
    "value": "2,450.40"
  },
  {
    "code": "BMPP0527",
    "value": "40,883.14"
  },
  {
    "code": "BMPP0139",
    "value": "19,450.30"
  },
  {
    "code": "BMPP0715",
    "value": "5,000.00"
  },
  {
    "code": "BMPP0716",
    "value": "11,487.00"
  },
  {
    "code": "BMPP0717",
    "value": 855.86
  },
  {
    "code": "BMPP0718",
    "value": "6,306.31"
  },
  {
    "code": "BMPP0719",
    "value": "225,225.23"
  },
  {
    "code": "BMPP0185",
    "value": 280
  },
  {
    "code": "BMPP0606",
    "value": "405,931.00"
  },
  {
    "code": "BMPP0482",
    "value": "976,717.08"
  },
  {
    "code": "BMPP0611",
    "value": "688,739.00"
  },
  {
    "code": "BMPP0745",
    "value": 0
  },
  {
    "code": "BMPP0720",
    "value": "550,000.00"
  },
  {
    "code": "BMPP0254",
    "value": "267,750.00"
  },
  {
    "code": "BMPP0721",
    "value": "1,895,724.60"
  },
  {
    "code": "BMPP0480",
    "value": "2,300,000.00"
  },
  {
    "code": "BMPP0466",
    "value": "8,500,000.00"
  },
  {
    "code": "BMPP0722",
    "value": "2,137,500.00"
  },
  {
    "code": "BMPP0723",
    "value": "1,450,000.00"
  },
  {
    "code": "BMPP0724",
    "value": "4,500,000.00"
  },
  {
    "code": "BMPP0725",
    "value": "551,080.00"
  },
  {
    "code": "BMPP0726",
    "value": "1,300,000.00"
  },
  {
    "code": "BMPP0727",
    "value": "711,000.00"
  },
  {
    "code": "BMPP0738",
    "value": "1,200,000.00"
  },
  {
    "code": "BMPP0617",
    "value": "2,478,252.50"
  },
  {
    "code": "BMPP0610",
    "value": "2,220,450.70"
  },
  {
    "code": "BMPP0728",
    "value": "6,500.00"
  },
  {
    "code": "BMPP0737",
    "value": "54,100.00"
  },
  {
    "code": "BMPP0660",
    "value": 0
  },
  {
    "code": "BMPP0661",
    "value": 0
  },
  {
    "code": "BMPP0666",
    "value": 0
  },
  {
    "code": "BMPP0662",
    "value": 0
  },
  {
    "code": "BMPP0663",
    "value": 0
  },
  {
    "code": "BMPP0664",
    "value": 0
  },
  {
    "code": "BMPP0665",
    "value": 0
  },
  {
    "code": "BMPP0667",
    "value": 0
  },
  {
    "code": "BMPP0259",
    "value": 0
  },
  {
    "code": "BMPP0669",
    "value": 0
  },
  {
    "code": "BMPP0040",
    "value": 0
  },
  {
    "code": "BMPP0668",
    "value": 0
  },
  {
    "code": "BMPP0007",
    "value": 0
  },
  {
    "code": "BMPP0674",
    "value": 0
  },
  {
    "code": "BMPP0554",
    "value": 0
  },
  {
    "code": "BMPP0548",
    "value": 0
  },
  {
    "code": "BMPP0675",
    "value": 0
  },
  {
    "code": "BMPP0029",
    "value": 0
  },
  {
    "code": "BMPP0677",
    "value": 0
  },
  {
    "code": "BMPP0678",
    "value": 0
  },
  {
    "code": "BMPP0045",
    "value": 0
  },
  {
    "code": "BMPP0682",
    "value": 0
  },
  {
    "code": "BMPP0190",
    "value": 0
  },
  {
    "code": "BMPP0231",
    "value": 0
  },
  {
    "code": "BMPP0232",
    "value": 0
  },
  {
    "code": "BMPP0684",
    "value": 0
  },
  {
    "code": "BMPP0234",
    "value": 0
  },
  {
    "code": "BMPP0233",
    "value": 0
  },
  {
    "code": "BMPP0679",
    "value": 0
  },
  {
    "code": "BMPP0681",
    "value": 0
  },
  {
    "code": "BMPP0283",
    "value": 0
  },
  {
    "code": "BMPP0174",
    "value": 0
  },
  {
    "code": "BMPP0685",
    "value": 0
  },
  {
    "code": "BMPP0686",
    "value": 0
  },
  {
    "code": "BMPP0687",
    "value": 0
  },
  {
    "code": "BMPP0236",
    "value": 0
  },
  {
    "code": "BMPP0692",
    "value": 0
  },
  {
    "code": "BMPP0689",
    "value": 0
  },
  {
    "code": "BMPP0691",
    "value": 0
  },
  {
    "code": "BMPP0690",
    "value": 0
  },
  {
    "code": "BMPP0739",
    "value": 0
  },
  {
    "code": "BMPP0740",
    "value": 0
  },
  {
    "code": "BMPP0742",
    "value": 0
  },
  {
    "code": "BMPP0741",
    "value": 0
  },
  {
    "code": "BMPP0275",
    "value": 0
  },
  {
    "code": "BMPP0743",
    "value": 0
  },
  {
    "code": "BMPP0389",
    "value": 0
  },
  {
    "code": "BMPP0239",
    "value": 0
  },
  {
    "code": "BMPP0543",
    "value": 0
  },
  {
    "code": "BMPP0133",
    "value": 0
  },
  {
    "code": "BMPP0168",
    "value": 0
  }
]';

        $data = json_decode($json, true);

        \DB::beginTransaction();

        foreach ($data as $row) {
            $item = Item::where('code', $row['code'])->first();
            $value = str_replace(',', '', $row['value']); // COGS
            echo $row['code'] . ' => ' . $row['value'] . PHP_EOL;
            
            if ($item) {
                $inventories = Inventory::orderBy('form_date', 'desc')
                    ->orderBy('formulir_id', 'desc')
                    ->orderBy('id', 'desc')
                    ->where('item_id', '=', $item->id)
                    ->get()
                    ->unique(function ($inventory) {
                        return $inventory['item_id'].$inventory['warehouse_id'];
                    });

                $this->comment('Processing item ' . $item->code . ' with COGS ' . $value . ' and total inventories: ' . count($inventories));
                foreach ($inventories as $inventory) {
                    $last = Inventory::where('item_id', '=', $inventory->item_id)
                        ->where('form_date', '<', '2026-06-30 23:59:59')
                        ->where('warehouse_id', '=', $inventory->warehouse_id)
                        ->orderBy('form_date', 'desc')
                        ->orderBy('formulir_id', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    $lastVal = Inventory::where('item_id', '=', $inventory->item_id)
                        ->where('form_date', '<', '2026-06-30 23:59:59')
                        ->orderBy('form_date', 'desc')
                        ->orderBy('formulir_id', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$last) {
                        $this->comment('No inventory found for item ' . $item->code . ' in warehouse ' . $inventory->warehouse_id);
                        continue;
                    } else {
                        $this->comment('Last inventory for item ' . $item->code . ' in warehouse ' . $inventory->warehouse_id . ': quantity = ' . $last->total_quantity . ', cogs = ' . $last->cogs);
                    }

                    if ($last->total_quantity == 0) {
                        $this->comment('No inventory quantity for item ' . $item->code . ' in warehouse ' . $inventory->warehouse_id);
                        continue;
                    }
                        
                    // TODO: Delete all item from warehouse to, so cogs, total quantity, total value is reset to 0
                    $form_date = '2026-06-30 23:59:59';
                    $form_number = FormulirHelper::number('point-inventory-stock-correction', $form_date);

                    $formulir = new Formulir;
                    $formulir->form_date = $form_date;
                    $formulir->created_at = $form_date;
                    $formulir->updated_at = $form_date;
                    $formulir->form_number = $form_number['form_number'];
                    $formulir->form_raw_number = $form_number['raw'];
                    $formulir->notes = 'Cutoff Stock 2026-07-31';
                    $formulir->approval_to = 1;
                    $formulir->approval_status = 1;
                    $formulir->approval_message = '';
                    $formulir->created_by = 1;
                    $formulir->updated_by = 1;
                    if (!$formulir->save()) {
                        gritter_error('create has been failed', false);
                    }

                    $stock_correction = new StockCorrection;
                    $stock_correction->formulir_id = $formulir->id;
                    $stock_correction->created_at = $form_date;
                    $stock_correction->updated_at = $form_date;
                    $stock_correction->warehouse_id = $inventory->warehouse_id;
                    $stock_correction->save();
                    
                    $stock_correction_item = new StockCorrectionItem;
                    $stock_correction_item->point_inventory_stock_correction_id = $stock_correction->id;
                    $stock_correction_item->item_id = $item->id;
                    $stock_correction_item->stock_in_database = $last->total_quantity;
                    $stock_correction_item->quantity_correction = $last->total_quantity * -1;
                    $stock_correction_item->correction_notes = 'Cutoff Stock 2026-07-31';
                    $unit = $stock_correction_item->item->unit()->first();
                    $stock_correction_item->unit = $unit->name;
                    $stock_correction_item->converter = $unit->converter;
                    $stock_correction_item->save();

                    $inventory = new Inventory;
                    $inventory->form_date = '2026-06-30 23:59:59';
                    $inventory->formulir_id = $stock_correction->formulir_id;
                    $inventory->warehouse_id = $stock_correction->warehouse_id;
                    $inventory->item_id = $stock_correction_item->item_id;
                    $inventory->quantity = $stock_correction_item->quantity_correction;
                    $inventory->price = $lastVal->cogs ?? 0;
                    
                    if ($inventory->quantity < 0) {
                        $inventory->quantity *= -1;
                        $inventory_helper = new InventoryHelper($inventory);
                        $inventory_helper->out0();
                    } else {
                        $inventory_helper = new InventoryHelper($inventory);
                        $inventory_helper->in();
                    }

                    StockCorrectionHelper::updateJournal($stock_correction);
                }
            }
        }

        foreach ($data as $row) {
            $item = Item::where('code', $row['code'])->first();
            $value = str_replace(',', '', $row['value']); // COGS
            echo $row['code'] . ' => ' . $row['value'] . PHP_EOL;
            
            if ($item) {
                $inventories = Inventory::orderBy('form_date', 'desc')
                    ->orderBy('formulir_id', 'desc')
                    ->where('item_id', '=', $item->id)
                    ->get()
                    ->unique(function ($inventory) {
                        return $inventory['item_id'].$inventory['warehouse_id'];
                    });

                $this->comment('Processing item ' . $item->code . ' with COGS ' . $value . ' and total inventories: ' . count($inventories));
                foreach ($inventories as $inventory) {
                    $last = Inventory::where('item_id', '=', $inventory->item_id)
                        ->where('form_date', '<', '2026-06-30 23:59:59')
                        ->where('warehouse_id', '=', $inventory->warehouse_id)
                        ->orderBy('form_date', 'desc')
                        ->orderBy('formulir_id', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$last) {
                        $this->comment('No inventory found for item ' . $item->code . ' in warehouse ' . $inventory->warehouse_id);
                        continue;
                    } else {
                        $this->comment('Last inventory for item ' . $item->code . ' in warehouse ' . $inventory->warehouse_id . ': quantity = ' . $last->total_quantity . ', cogs = ' . $last->cogs);
                    }

                    if ($last->total_quantity == 0) {
                        $this->comment('No inventory quantity for item ' . $item->code . ' in warehouse ' . $inventory->warehouse_id);
                        continue;
                    }
                        
                    // TODO: Delete all item from warehouse to, so cogs, total quantity, total value is reset to 0
                    $form_date = '2026-07-01 00:00:00';
                    $form_number = FormulirHelper::number('point-inventory-stock-correction', $form_date);

                    $formulir = new Formulir;
                    $formulir->form_date = $form_date;
                    $formulir->created_at = $form_date;
                    $formulir->updated_at = $form_date;
                    $formulir->form_number = $form_number['form_number'];
                    $formulir->form_raw_number = $form_number['raw'];
                    $formulir->notes = 'Cutoff Stock 2026-07-01';
                    $formulir->approval_to = 1;
                    $formulir->approval_status = 1;
                    $formulir->approval_message = '';
                    $formulir->created_by = 1;
                    $formulir->updated_by = 1;
                    if (!$formulir->save()) {
                        gritter_error('create has been failed', false);
                    }

                    $stock_correction = new StockCorrection;
                    $stock_correction->formulir_id = $formulir->id;
                    $stock_correction->created_at = $form_date;
                    $stock_correction->updated_at = $form_date;
                    $stock_correction->warehouse_id = $inventory->warehouse_id;
                    $stock_correction->save();
                    
                    $stock_correction_item = new StockCorrectionItem;
                    $stock_correction_item->point_inventory_stock_correction_id = $stock_correction->id;
                    $stock_correction_item->item_id = $item->id;
                    $stock_correction_item->stock_in_database = $last->total_quantity;
                    $stock_correction_item->quantity_correction = $last->total_quantity;
                    $stock_correction_item->correction_notes = 'Cutoff Stock 2026-07-01';
                    $unit = $stock_correction_item->item->unit()->first();
                    $stock_correction_item->unit = $unit->name;
                    $stock_correction_item->converter = $unit->converter;
                    $stock_correction_item->save();

                    $inventory = new Inventory;
                    $inventory->form_date = '2026-07-01 00:00:00';
                    $inventory->formulir_id = $stock_correction->formulir_id;
                    $inventory->warehouse_id = $stock_correction->warehouse_id;
                    $inventory->item_id = $stock_correction_item->item_id;
                    $inventory->quantity = $stock_correction_item->quantity_correction;
                    $inventory->price = $value ?? 0;
                    $inventory->cogs = $value ?? 0;
                    
                    if ($inventory->quantity < 0) {
                        $inventory->quantity *= -1;
                        $inventory_helper = new InventoryHelper($inventory);
                        $inventory_helper->out();
                    } else {
                        $inventory_helper = new InventoryHelper($inventory);
                        $inventory_helper->in();
                    }

                    StockCorrectionHelper::updateJournal($stock_correction);
                }
            }
        }

        \DB::commit();
    }
}