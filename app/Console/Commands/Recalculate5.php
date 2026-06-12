<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointSales\Models\Sales\Retur;
use Point\Framework\Models\Master\Warehouse;

class Recalculate5 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:5';

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
            "code": "10601-1",
            "value": "2,180,278.37"
        },
        {
            "code": "10601-2",
            "value": "799,099.35"
        },
        {
            "code": "10601-3",
            "value": "2,443,621.19"
        },
        {
            "code": "10601-4",
            "value": "7,823,419.50"
        },
        {
            "code": "10601-5",
            "value": "20,129,558.90"
        },
        {
            "code": "10601-6",
            "value": "13,005,219.68"
        },
        {
            "code": "10601-7",
            "value": "22,667,753.80"
        },
        {
            "code": "10601-8",
            "value": "6,069,951.26"
        },
        {
            "code": "10601-9",
            "value": "48,045.97"
        },
        {
            "code": "10601-10",
            "value": "4,401,673.38"
        },
        {
            "code": "10601-11",
            "value": "1,428,613.72"
        },
        {
            "code": "10601-12",
            "value": "261,019,731.60"
        },
        {
            "code": "10601-13",
            "value": "432,889.02"
        },
        {
            "code": "10601-15",
            "value": "3,400,608,059.57"
        },
        {
            "code": "10601-16",
            "value": "1,160,131.62"
        },
        {
            "code": "10601-17",
            "value": "849,721.05"
        },
        {
            "code": "10601-18",
            "value": "1,072,410.11"
        },
        {
            "code": "10601-19",
            "value": "374,908.07"
        },
        {
            "code": "10601-11-1",
            "value": "7,890,636.74"
        },
        {
            "code": "10601-23",
            "value": "443,839.97"
        },
        {
            "code": "10601-25",
            "value": "141,342.32"
        },
        {
            "code": "10601-27",
            "value": "1,695,933.83"
        },
        {
            "code": "10603-01",
            "value": "7,246,885.88"
        },
        {
            "code": "10603-02",
            "value": "21,480,652.05"
        },
        {
            "code": "10603-03",
            "value": "12,725,313.61"
        },
        {
            "code": "10603-04",
            "value": "5,117.79"
        },
        {
            "code": "10603-05",
            "value": "21,130,302.80"
        },
        {
            "code": "10603-06",
            "value": "2,597,275.14"
        },
        {
            "code": "10603-09",
            "value": "12,889,337.58"
        },
        {
            "code": "10603-11",
            "value": "988,006.52"
        },
        {
            "code": "10603-14",
            "value": "1,376,798.14"
        },
        {
            "code": "10603-15",
            "value": "2,520,456.92"
        },
        {
            "code": "10603-17",
            "value": "5,626,453.18"
        },
        {
            "code": "10603-73",
            "value": "5,344,094.58"
        },
        {
            "code": "10603-21",
            "value": "0.00"
        },
        {
            "code": "10603-24",
            "value": "994,606.67"
        },
        {
            "code": "10603-25",
            "value": "0.01"
        },
        {
            "code": "10603-27",
            "value": "13,112,551.21"
        },
        {
            "code": "10603-29",
            "value": "3,604,123.74"
        },
        {
            "code": "10603-41",
            "value": "17,531,911.06"
        },
        {
            "code": "10603-43",
            "value": "4,355,937.91"
        },
        {
            "code": "10603-44",
            "value": "448,115.07"
        },
        {
            "code": "10603-90",
            "value": "4,023,711.53"
        },
        {
            "code": "10603-77",
            "value": "31,350,621.30"
        },
        {
            "code": "10603-82",
            "value": "121,831,372.76"
        },
        {
            "code": "10604-01",
            "value": "151,031,411.35"
        },
        {
            "code": "10604-02",
            "value": "867,636.64"
        },
        {
            "code": "10604-03",
            "value": "482,677.07"
        },
        {
            "code": "10604-07",
            "value": "2,661,476.94"
        },
        {
            "code": "10604-08",
            "value": "38,675,904.27"
        },
        {
            "code": "10604-09",
            "value": "45,759,551.43"
        },
        {
            "code": "10604-10",
            "value": "9,132,632.22"
        },
        {
            "code": "10604-11",
            "value": "2,354,464.33"
        },
        {
            "code": "10604-12",
            "value": "7,169,278.92"
        },
        {
            "code": "10604-13",
            "value": "2,406,997.43"
        },
        {
            "code": "10604-14",
            "value": "1,213,101,239.16"
        },
        {
            "code": "10604-15",
            "value": "9,909,300.00"
        },
        {
            "code": "10604-16",
            "value": "1,792,008.88"
        },
        {
            "code": "10604-21",
            "value": "9,051,300.00"
        },
        {
            "code": "10604-22",
            "value": "83,723,920.00"
        },
        {
            "code": "1-77",
            "value": "549,667.39"
        },
        {
            "code": "1-78",
            "value": "3,471,287.10"
        },
        {
            "code": "10604-27",
            "value": "1,582,500.00"
        },
        {
            "code": "10604-28",
            "value": "1,877,626,507.20"
        },
        {
            "code": "10603-78",
            "value": "149,142,508.21"
        },
        {
            "code": "10603-80",
            "value": "17,430,278.27"
        },
        {
            "code": "10603-81",
            "value": "4,475,546.83"
        },
        {
            "code": "10605-01",
            "value": "2,667,441.95"
        },
        {
            "code": "10605-04",
            "value": "27,045,315.00"
        },
        {
            "code": "10605-06",
            "value": "1,560,000.00"
        },
        {
            "code": "10605-07",
            "value": "1,080,000.00"
        },
        {
            "code": "PM-A06",
            "value": "51,100,000.00"
        },
        {
            "code": "PM-A07",
            "value": "363,478.80"
        },
        {
            "code": "PM-A08",
            "value": "526,552.17"
        },
        {
            "code": "PM-A09",
            "value": "1,460,956.43"
        },
        {
            "code": "PM-A10",
            "value": "1,791,292.66"
        },
        {
            "code": "PM-A12",
            "value": "10,980,000.00"
        },
        {
            "code": "PM-A13",
            "value": "1,197,314.88"
        },
        {
            "code": "PM-A14",
            "value": "826,800.00"
        },
        {
            "code": "PM-A15",
            "value": "728,907.76"
        },
        {
            "code": "PM-B15",
            "value": "1,306,295.08"
        },
        {
            "code": "PM-B16",
            "value": "4,284,641.55"
        },
        {
            "code": "PM-B17",
            "value": "41,421,778.04"
        },
        {
            "code": "PM-C01",
            "value": "883,500.00"
        },
        {
            "code": "PM-C02",
            "value": "655,661.56"
        },
        {
            "code": "PM-C03",
            "value": "820,190.91"
        },
        {
            "code": "PM-C04",
            "value": "475,000.00"
        },
        {
            "code": "PM-C05",
            "value": "12,324.76"
        },
        {
            "code": "PM-C06",
            "value": "219,743.18"
        },
        {
            "code": "PM-A20",
            "value": "1,541,873.43"
        },
        {
            "code": "PM-A21",
            "value": "834,966.12"
        },
        {
            "code": "PM-B19",
            "value": "417,685.93"
        },
        {
            "code": "PM-B20",
            "value": "420,000.00"
        },
        {
            "code": "PM-B21",
            "value": "429,000.00"
        },
        {
            "code": "PM-B22",
            "value": "425,692.12"
        },
        {
            "code": "PM-A16",
            "value": "234,000.00"
        },
        {
            "code": "PM-C07",
            "value": "990,000.00"
        },
        {
            "code": "PM-A17",
            "value": "89,169,247.78"
        },
        {
            "code": "PM-C08",
            "value": "2,131,500.00"
        },
        {
            "code": "PM-A18",
            "value": "472,600.00"
        },
        {
            "code": "PM-B27",
            "value": "82,957.95"
        },
        {
            "code": "PM-B28",
            "value": "100,856.02"
        },
        {
            "code": "PM-B29",
            "value": "20,108.33"
        },
        {
            "code": "PM-B30",
            "value": "23,490.32"
        },
        {
            "code": "PM-B31",
            "value": "64,600.00"
        },
        {
            "code": "PM-B32",
            "value": "68,508.48"
        },
        {
            "code": "PM-B33",
            "value": "79,267.31"
        },
        {
            "code": "PM-B34",
            "value": "78,681.48"
        },
        {
            "code": "PM-B35",
            "value": "425,000.00"
        },
        {
            "code": "PM-B36",
            "value": "425,000.00"
        },
        {
            "code": "PM-B37",
            "value": "425,000.00"
        },
        {
            "code": "PM-B38",
            "value": "425,000.00"
        },
        {
            "code": "PM-B39",
            "value": "2,316,584.91"
        },
        {
            "code": "pm-b45",
            "value": "7,200,000.00"
        },
        {
            "code": "B0008",
            "value": "22,000,000.00"
        },
        {
            "code": "B0009",
            "value": "7,653,060.00"
        },
        {
            "code": "B0010",
            "value": "13,500,000.00"
        },
        {
            "code": "B0011",
            "value": "12,000,000.00"
        },
        {
            "code": "B0012",
            "value": "31,003,251.00"
        },
        {
            "code": "B0013",
            "value": "60,631,000.00"
        },
        {
            "code": "B-0001",
            "value": "29,579,000.00"
        },
        {
            "code": "B-0002",
            "value": "134,750,160.00"
        },
        {
            "code": "B-0003",
            "value": "101,500,000.00"
        },
        {
            "code": "B-0004",
            "value": "14,000,000.00"
        },
        {
            "code": "B-0005",
            "value": "38,706,000.00"
        },
        {
            "code": "B-0006",
            "value": "18,670,400.00"
        },
        {
            "code": "B-0007",
            "value": "12,755,102.00"
        },
        {
            "code": "IL0001",
            "value": "359,541,000.00"
        },
        {
            "code": "IL0002",
            "value": "13,920,000.00"
        },
        {
            "code": "IL0003",
            "value": "13,100,000.00"
        },
        {
            "code": "IL0004",
            "value": "27,963,500.00"
        },
        {
            "code": "Mp0001",
            "value": "259,600,000.00"
        },
        {
            "code": "MP0002",
            "value": "115,500,000.00"
        },
        {
            "code": "MP0003",
            "value": "200,200,000.00"
        },
        {
            "code": "MP0004",
            "value": "10,412,875.00"
        },
        {
            "code": "MP0005",
            "value": "146,850,000.00"
        },
        {
            "code": "MP0006",
            "value": "2,122,504,395.00"
        },
        {
            "code": "MP0007",
            "value": "421,736,500.00"
        },
        {
            "code": "MP0008",
            "value": "1,050,000,000.00"
        },
        {
            "code": "MP0009",
            "value": "400,000,000.00"
        },
        {
            "code": "MP0010",
            "value": "1,947,000,000.00"
        },
        {
            "code": "MP0011",
            "value": "3,950,000.00"
        },
        {
            "code": "MP0012",
            "value": "3,425,265.00"
        },
        {
            "code": "MP0013",
            "value": "2,898,960,873.00"
        },
        {
            "code": "MP0014",
            "value": "964,920,000.00"
        },
        {
            "code": "MP0015",
            "value": "235,868,435.00"
        },
        {
            "code": "MP0016",
            "value": "873,810,000.00"
        },
        {
            "code": "MP0017",
            "value": "81,517,825.00"
        },
        {
            "code": "MP0019",
            "value": "12,721,588.00"
        },
        {
            "code": "MP0020",
            "value": "37,317,720.00"
        },
        {
            "code": "MP0021",
            "value": "159,891,930.00"
        },
        {
            "code": "MP0022",
            "value": "6,000,000.00"
        },
        {
            "code": "MP0023",
            "value": "26,070,000.00"
        },
        {
            "code": "MP0024",
            "value": "65,017,750.00"
        },
        {
            "code": "MP0025",
            "value": "77,104,610.00"
        },
        {
            "code": "MP0026",
            "value": "15,493,280.00"
        },
        {
            "code": "MP0027",
            "value": "63,173,600.00"
        },
        {
            "code": "MP0028",
            "value": "200,640,000.00"
        },
        {
            "code": "MP0029",
            "value": "8,500,000.00"
        },
        {
            "code": "MP0030",
            "value": "14,600,500.00"
        },
        {
            "code": "MP0031",
            "value": "35,000,000.00"
        },
        {
            "code": "MP0032",
            "value": "167,000,000.00"
        },
        {
            "code": "MP0033 FRB-770II* CONTINOUS SEALER STAINLESS STEEL BODY",
            "value": "3,873,873.87"
        },
        {
            "code": "MP0034 TP-8022 SEMI AUTO STRAPPING MACHINE STANDARD",
            "value": "6,126,126.13"
        },
        {
            "code": "mp0035 FZ-5000 SEMI AUTO INTELLGENT WEIGHING FILLER",
            "value": "25,369,370.00"
        },
        {
            "code": "MP0036",
            "value": "26,000,000.00"
        },
        {
            "code": "mp0037",
            "value": "26,000,000.00"
        },
        {
            "code": "MP0038",
            "value": "26,000,000.00"
        },
        {
            "code": "MP0039",
            "value": "2,000,000.00"
        },
        {
            "code": "MP0040",
            "value": "9,000,000.00"
        },
        {
            "code": "MP0041",
            "value": "10,500,000.00"
        },
        {
            "code": "MP0033",
            "value": "10,000,000.00"
        },
        {
            "code": "mp059",
            "value": "14,758,900.00"
        },
        {
            "code": "mp060",
            "value": "1,306,306.00"
        },
        {
            "code": "ML0001",
            "value": "61,655,800.00"
        },
        {
            "code": "ML0002",
            "value": "764,294.00"
        },
        {
            "code": "ML0003",
            "value": "3,088,000.00"
        },
        {
            "code": "ml0004",
            "value": "1,306,306.00"
        },
        {
            "code": "IK0050",
            "value": "1,600,000.00"
        },
        {
            "code": "IP0001",
            "value": "7,150,000.00"
        },
        {
            "code": "IP0002",
            "value": "1,645,000.00"
        },
        {
            "code": "IP0003",
            "value": "6,500,000.00"
        },
        {
            "code": "IP0004",
            "value": "3,360,000.00"
        },
        {
            "code": "IP0005",
            "value": "1,704,000.00"
        },
        {
            "code": "IP0006",
            "value": "4,600,000.00"
        },
        {
            "code": "IP0007",
            "value": "2,250,000.00"
        },
        {
            "code": "IP0008",
            "value": "4,300,000.00"
        },
        {
            "code": "IP0009",
            "value": "56,000,000.00"
        },
        {
            "code": "IP0010",
            "value": "1,930,000.00"
        },
        {
            "code": "IP0011",
            "value": "13,600,000.00"
        },
        {
            "code": "IP0012",
            "value": "30,650,000.00"
        },
        {
            "code": "IP0013",
            "value": "1,500,000.00"
        },
        {
            "code": "IP0014",
            "value": "4,800,000.00"
        },
        {
            "code": "IP0015",
            "value": "5,700,000.00"
        },
        {
            "code": "IP0016",
            "value": "12,800,000.00"
        },
        {
            "code": "IP0017",
            "value": "3,600,000.00"
        },
        {
            "code": "IP0018",
            "value": "825,000.00"
        },
        {
            "code": "IP0019",
            "value": "2,351,000.00"
        },
        {
            "code": "IP0020",
            "value": "985,000.00"
        },
        {
            "code": "IP0021",
            "value": "8,400,000.00"
        },
        {
            "code": "IP0022",
            "value": "1,600,000.00"
        },
        {
            "code": "IP0023",
            "value": "3,465,000.00"
        },
        {
            "code": "IP0024",
            "value": "11,000,000.00"
        },
        {
            "code": "IP0025",
            "value": "9,800,000.00"
        },
        {
            "code": "IP0028",
            "value": "1,200,000.00"
        },
        {
            "code": "IP0029",
            "value": "2,295,000.00"
        },
        {
            "code": "IP0030",
            "value": "3,600,000.00"
        },
        {
            "code": "IP0031",
            "value": "6,501,000.00"
        },
        {
            "code": "IP0032",
            "value": "37,950,000.00"
        },
        {
            "code": "IP0033",
            "value": "7,000,000.00"
        },
        {
            "code": "IP0034",
            "value": "4,594,000.00"
        },
        {
            "code": "IP0035",
            "value": "24,500,000.00"
        },
        {
            "code": "IP0036",
            "value": "14,928,571.00"
        },
        {
            "code": "IP0037",
            "value": "2,600,000.00"
        },
        {
            "code": "IP0038",
            "value": "2,380,000.00"
        },
        {
            "code": "IP0039",
            "value": "2,452,000.00"
        },
        {
            "code": "IP0040",
            "value": "2,047,000.00"
        },
        {
            "code": "IP0042",
            "value": "52,969,999.99"
        },
        {
            "code": "IP0043",
            "value": "4,995,000.00"
        },
        {
            "code": "IP0044",
            "value": "1,151,600.00"
        },
        {
            "code": "IP0045",
            "value": "3,500,000.00"
        },
        {
            "code": "brt0020",
            "value": "975,000.00"
        },
        {
            "code": "IP0047",
            "value": "170,000.00"
        },
        {
            "code": "IP0048",
            "value": "430,000.00"
        },
        {
            "code": "IP0049",
            "value": "725,000.00"
        },
        {
            "code": "IP0050",
            "value": "1,000,000.00"
        },
        {
            "code": "IP0052",
            "value": "535,400.00"
        },
        {
            "code": "IP0054",
            "value": "2,500,000.00"
        },
        {
            "code": "IP0055",
            "value": "3,800,000.00"
        },
        {
            "code": "IP0056",
            "value": "800,000.00"
        },
        {
            "code": "IP0057",
            "value": "2,810,000.00"
        },
        {
            "code": "IP0058",
            "value": "4,130,000.00"
        },
        {
            "code": "IP0059",
            "value": "17,187,500.00"
        },
        {
            "code": "IP0060",
            "value": "19,951,500.00"
        },
        {
            "code": "IP0061",
            "value": "1,400,000.00"
        },
        {
            "code": "IP0063",
            "value": "8,250,000.00"
        },
        {
            "code": "IP0064",
            "value": "207,900.00"
        },
        {
            "code": "IP0065",
            "value": "1,418,000.00"
        },
        {
            "code": "IP0066",
            "value": "963,000.00"
        },
        {
            "code": "IP0068",
            "value": "3,580,000.00"
        },
        {
            "code": "IP0071",
            "value": "140,500,000.00"
        },
        {
            "code": "IP0072",
            "value": "2,250,000.00"
        },
        {
            "code": "IP0073",
            "value": "2,815,000.00"
        },
        {
            "code": "IP0074",
            "value": "2,800,000.00"
        },
        {
            "code": "IP0076",
            "value": "1,500,000.00"
        },
        {
            "code": "IP0077",
            "value": "5,500,000.00"
        },
        {
            "code": "IP0078",
            "value": "5,700,000.00"
        },
        {
            "code": "IP0079",
            "value": "300,000.00"
        },
        {
            "code": "IP0081",
            "value": "16,800,000.00"
        },
        {
            "code": "IP0083",
            "value": "2,761,700.00"
        },
        {
            "code": "IP0084",
            "value": "235,000.00"
        },
        {
            "code": "IP0085",
            "value": "1,925,000.00"
        },
        {
            "code": "IP0086",
            "value": "2,380,000.00"
        },
        {
            "code": "IP0087",
            "value": "8,500,000.00"
        },
        {
            "code": "IP0088",
            "value": "1,269,000.00"
        },
        {
            "code": "IP0089",
            "value": "4,400,000.00"
        },
        {
            "code": "IP0092",
            "value": "5,868,200.00"
        },
        {
            "code": "IP0093",
            "value": "3,000,000.00"
        },
        {
            "code": "IP0094",
            "value": "3,300,000.00"
        },
        {
            "code": "IP0096",
            "value": "1,568,500.00"
        },
        {
            "code": "IP0097",
            "value": "1,187,500.00"
        },
        {
            "code": "IP0098",
            "value": "15,000,000.00"
        },
        {
            "code": "IP0099 box container plastik hijau 80liter",
            "value": "1,198,199.00"
        },
        {
            "code": "ip0102",
            "value": "1,688,434.00"
        },
        {
            "code": "[ip0103]",
            "value": "1,750,000.00"
        },
        {
            "code": "IP0104",
            "value": "72,500,000.00"
        },
        {
            "code": "ip-0110",
            "value": "5,172,449.00"
        },
        {
            "code": "IP0114",
            "value": "25,675,676.00"
        },
        {
            "code": "IP0200",
            "value": "573,355.00"
        },
        {
            "code": "IP0201",
            "value": "1,294,600.00"
        },
        {
            "code": "IP0202",
            "value": "411,550.00"
        },
        {
            "code": "IP0203",
            "value": "1,290,000.00"
        },
        {
            "code": "IP0204",
            "value": "260,000.00"
        },
        {
            "code": "IK0001",
            "value": "29,397,000.00"
        },
        {
            "code": "IK0002",
            "value": "2,950,000.00"
        },
        {
            "code": "IK0003",
            "value": "2,900,000.00"
        },
        {
            "code": "IK0004",
            "value": "5,360,000.00"
        },
        {
            "code": "IK0005",
            "value": "2,070,000.00"
        },
        {
            "code": "IK0006",
            "value": "2,500,000.00"
        },
        {
            "code": "IK0007",
            "value": "7,200,000.00"
        },
        {
            "code": "IK0008",
            "value": "5,945,000.00"
        },
        {
            "code": "IK0009",
            "value": "450,000.00"
        },
        {
            "code": "IK0010",
            "value": "1,000,000.00"
        },
        {
            "code": "IK0011",
            "value": "1,800,000.00"
        },
        {
            "code": "IK0012",
            "value": "2,900,000.00"
        },
        {
            "code": "IK0013",
            "value": "850,000.00"
        },
        {
            "code": "IK0014",
            "value": "3,550,000.00"
        },
        {
            "code": "IK0015",
            "value": "1,600,000.00"
        },
        {
            "code": "IK0016",
            "value": "2,650,000.00"
        },
        {
            "code": "IK0018",
            "value": "28,000,000.00"
        },
        {
            "code": "IK0019",
            "value": "17,089,000.00"
        },
        {
            "code": "IK0021",
            "value": "690,000.00"
        },
        {
            "code": "IK0022",
            "value": "8,673,469.00"
        },
        {
            "code": "IK0023",
            "value": "1,575,000.00"
        },
        {
            "code": "IK0024",
            "value": "6,248,000.00"
        },
        {
            "code": "IK0025",
            "value": "1,167,932.00"
        },
        {
            "code": "IK0026",
            "value": "1,594,595.00"
        },
        {
            "code": "IK0027",
            "value": "3,500,000.00"
        },
        {
            "code": "IK0028",
            "value": "10,000,000.00"
        },
        {
            "code": "ik0032",
            "value": "261,000.00"
        },
        {
            "code": "ik0033",
            "value": "4,728,000.00"
        },
        {
            "code": "ik0034",
            "value": "272,000.00"
        },
        {
            "code": "IK0035",
            "value": "1,783,000.00"
        },
        {
            "code": "ik0036",
            "value": "800,000.00"
        },
        {
            "code": "ik0037",
            "value": "1,150,000.00"
        },
        {
            "code": "K0001",
            "value": "80,000,000.00"
        },
        {
            "code": "10603-67",
            "value": "0.00"
        },
        {
            "code": "10601-14",
            "value": "568,538.39"
        },
        {
            "code": "10601-24",
            "value": "174,177.64"
        },
        {
            "code": "10603-26",
            "value": "-88,394,170.22"
        },
        {
            "code": "10603-65",
            "value": "0.01"
        },
        {
            "code": "10601-32",
            "value": "112,500.00"
        },
        {
            "code": "10601-33",
            "value": "112,500.00"
        },
        {
            "code": "10601-31",
            "value": "112,500.00"
        },
        {
            "code": "10601-35",
            "value": "126,000.00"
        },
        {
            "code": "10601-36",
            "value": "108,000.00"
        },
        {
            "code": "10601-34",
            "value": "112,500.00"
        },
        {
            "code": "JS0005",
            "value": "79,000.00"
        },
        {
            "code": "10603-68",
            "value": "0"
        },
        {
            "code": "10604-31",
            "value": "7,722,000.00"
        },
        {
            "code": "10604-32",
            "value": "7,546,000.00"
        },
        {
            "code": "10604-24",
            "value": "0"
        },
        {
            "code": "10604-23",
            "value": "0"
        },
        {
            "code": "10604-25",
            "value": "0"
        },
        {
            "code": "10604-26",
            "value": "0"
        },
        {
            "code": "10201-21",
            "value": "0"
        },
        {
            "code": "10201-30",
            "value": "0"
        },
        {
            "code": "10301-01",
            "value": "0.00"
        },
        {
            "code": "10301-03",
            "value": "0.00"
        },
        {
            "code": "10301-02",
            "value": "0.00"
        },
        {
            "code": "10202-03",
            "value": "294,000.00"
        },
        {
            "code": "10202-04",
            "value": "252,000.00"
        },
        {
            "code": "10202-05",
            "value": "115,000.00"
        },
        {
            "code": "IP0138",
            "value": "16,441,441.44"
        },
        {
            "code": "10401-02",
            "value": "401,922.28"
        },
        {
            "code": "10401-01",
            "value": "406,339.01"
        },
        {
            "code": "10401-03",
            "value": "406,339.01"
        },
        {
            "code": "10401-05",
            "value": "241,207.46"
        },
        {
            "code": "10401-06",
            "value": "239,358.20"
        },
        {
            "code": "10401-08",
            "value": "241,959.92"
        },
        {
            "code": "10401-13",
            "value": "19,738.21"
        },
        {
            "code": "10401-14",
            "value": "18,641.64"
        },
        {
            "code": "10401-16",
            "value": "19,738.21"
        },
        {
            "code": "10401-04",
            "value": "1,063,649.59"
        },
        {
            "code": "10401-09",
            "value": "228,215.77"
        },
        {
            "code": "10401-17",
            "value": "465,560.17"
        },
        {
            "code": "10401-10",
            "value": "233,469.39"
        },
        {
            "code": "10401-18",
            "value": "462,448.99"
        },
        {
            "code": "10401-11",
            "value": "233,402.49"
        },
        {
            "code": "10401-19",
            "value": "472,222.22"
        },
        {
            "code": "10401-20",
            "value": "369,642.86"
        },
        {
            "code": "10401-12",
            "value": "186,737.81"
        },
        {
            "code": "10604-30",
            "value": "151,096,634.30"
        },
        {
            "code": "10601-28",
            "value": "515,925.91"
        },
        {
            "code": "10401-07",
            "value": "626,640.27"
        },
        {
            "code": "10401-15",
            "value": "48,354.05"
        },
        {
            "code": "10604-33",
            "value": "306,344,500.00"
        },
        {
            "code": "pm-c17",
            "value": "2,700,000.00"
        },
        {
            "code": "10603-35",
            "value": "0"
        },
        {
            "code": "10603-69",
            "value": "0"
        },
        {
            "code": "10603-70",
            "value": "0"
        },
        {
            "code": "10603-71",
            "value": "0"
        },
        {
            "code": "10603-72",
            "value": "0"
        },
        {
            "code": "10603-91",
            "value": "0"
        },
        {
            "code": "10603-66",
            "value": "296,084.49"
        },
        {
            "code": "10603-92",
            "value": "16,146,381.66"
        },
        {
            "code": "10301-04",
            "value": "0.00"
        },
        {
            "code": "10603-93",
            "value": "0.10"
        },
        {
            "code": "10601-20",
            "value": "0.00"
        },
        {
            "code": "10603-94",
            "value": "-0.06"
        },
        {
            "code": "10301-05",
            "value": "0"
        },
        {
            "code": "10601-38",
            "value": "0.00"
        },
        {
            "code": "10603-95",
            "value": "11,681,137.28"
        },
        {
            "code": "10603-96",
            "value": "0.00"
        },
        {
            "code": "10603-97",
            "value": "0"
        },
        {
            "code": "10601-37",
            "value": "0.00"
        },
        {
            "code": "10601-39",
            "value": "2,357,748.54"
        },
        {
            "code": "IK0041",
            "value": "205,000.00"
        },
        {
            "code": "10601-29",
            "value": "0.00"
        },
        {
            "code": "10301-08",
            "value": "11,292,057.10"
        },
        {
            "code": "10604-35",
            "value": "57,606,780.00"
        },
        {
            "code": "10603-98",
            "value": "0"
        },
        {
            "code": "10603-99",
            "value": "0"
        },
        {
            "code": "10603-100",
            "value": "0"
        },
        {
            "code": "10601-41",
            "value": "0.00"
        },
        {
            "code": "10604-34",
            "value": "545,903,150.00"
        },
        {
            "code": "10603-105",
            "value": "36,187,475.51"
        },
        {
            "code": "10603-101",
            "value": "0"
        },
        {
            "code": "10603-102",
            "value": "334,243.96"
        },
        {
            "code": "10603-103",
            "value": "0.00"
        },
        {
            "code": "10603-104",
            "value": "0"
        },
        {
            "code": "IP0141",
            "value": "30,500,000.00"
        },
        {
            "code": "PM-B48",
            "value": "5,587,560.00"
        },
        {
            "code": "10601-40",
            "value": "19,345.66"
        },
        {
            "code": "PM-A23",
            "value": "220,000.00"
        },
        {
            "code": "PM-A24",
            "value": "34,725,000.00"
        }
        ]';

        $data = json_decode($json, true);

        foreach ($data as $row) {
            $item = Item::where('code', $row['code'])->first();
            $value = str_replace(',', '', $row['value']);
            // echo $row['code'] . ' => ' . $row['value'] . PHP_EOL;

            if ($item) {
                $inventory = Inventory::where('item_id', '=', $item->id)
                    ->where('form_date', '<', '2026-05-05')
                    ->orderBy('form_date', 'desc')
                    ->orderBy('formulir_id', 'desc')
                    ->first();

                if ($inventory) {
                    $inventory->total_value_all = $value;
                    if ($inventory->total_quantity_all == 0) {
                        $inventory->cogs = 0;
                    } else {
                        $inventory->cogs = $inventory->total_value_all / $inventory->total_quantity_all;
                        $inventory->save();
                    }
                }

                $list_inventory = Inventory::where('item_id', '=', $item->id)
                    ->where('form_date', '>=', $inventory->form_date)
                    ->orderBy('form_date', 'asc')
                    ->orderBy('formulir_id', 'asc')
                    ->get();

                $prevTotalQty = 0;
                $prevTotalVal = 0;
                $i=0;
                foreach($list_inventory as $index => $l_inventory) {
                    if ($i == 0) {
                        $i++;
                        $prevTotalQty = $l_inventory->total_quantity_all;
                        $prevTotalVal = $l_inventory->total_value_all;
                        continue;
                    }
                    if ($l_inventory->quantity < 0) {
                        if ($prevTotalQty == 0) {
                            $l_inventory->price = 0;
                        } else {
                            $l_inventory->price = $prevTotalVal / $prevTotalQty;
                        }
                    }
                    if ($l_inventory->quantity > 0) {
                        $this->comment($l_inventory->formulir->formulirable_type);
                        if ($l_inventory->formulir->formulirable_type === 'Point\PointInventory\Models\StockOpname\StockOpname' 
                            || $l_inventory->formulir->formulirable_type === 'Point\PointInventory\Models\StockCorrection\StockCorrection') {
                            // $this->comment('Stock Correction / Stock Opname');
                            if ($prevTotalQty == 0) {
                                $l_inventory->price = 0;
                            } else {
                                $l_inventory->price = $prevTotalVal / $prevTotalQty;
                            }
                        }
                    }
                    // $l_inventory->total_quantity_all = $prevTotalQty + $l_inventory->quantity;
                    $l_inventory->total_value_all = $prevTotalVal + ($l_inventory->quantity * $l_inventory->price);
                    if (!$l_inventory->total_quantity_all || $l_inventory->total_quantity_all == 0) {
                        $l_inventory->cogs = 0;
                    } else {
                        $l_inventory->cogs = $l_inventory->total_value_all / $l_inventory->total_quantity_all;
                    }
                    // $l_inventory->save();

                    $journals = Journal::where('form_journal_id', '=', $l_inventory->formulir_id)->get();

                    foreach ($journals as $journal) {
                        // $journal->debit = $l_inventory->total_value_all;
                        // $journal->credit = $l_inventory->total_value_all;
                        // $journal->save();
                        echo 'Update journal ' . $journal->id . ' => ' . $journal->debit . ' / ' . $journal->credit . PHP_EOL;
                    }

                    $prevTotalQty = $l_inventory->total_quantity_all;
                    $prevTotalVal = $l_inventory->total_value_all;
                }

                $list_inventory = Inventory::where('item_id', '=', $item->id)
                    ->where('form_date', '>=', $inventory->form_date)
                    ->orderBy('form_date', 'asc')
                    ->orderBy('formulir_id', 'asc')
                    ->get();

                foreach($list_inventory as $index => $l_inventory) {
                    $l_inventory->total_value = $l_inventory->total_quantity * $l_inventory->cogs;
                    // $l_inventory->save();
                }
            }
        }
    }
}