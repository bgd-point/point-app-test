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

class Recalculate5kb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:5kb';

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
    "code": "001-683",
    "value": "620000.00"
  },
  {
    "code": "001-688",
    "value": "620000.00"
  },
  {
    "code": "001-703",
    "value": "25411.00"
  },
  {
    "code": "001-103",
    "value": "118800.00"
  },
  {
    "code": "001-629",
    "value": "80767.00"
  },
  {
    "code": "001-630",
    "value": "80767.00"
  },
  {
    "code": "001-631",
    "value": "80767.00"
  },
  {
    "code": "001-244",
    "value": "840000.00"
  },
  {
    "code": "001-243",
    "value": "630000.00"
  },
  {
    "code": "001-487",
    "value": "1071402.00"
  },
  {
    "code": "001-110",
    "value": "25000.00"
  },
  {
    "code": "001-236",
    "value": "22300.00"
  },
  {
    "code": "001-77",
    "value": "10389583.00"
  },
  {
    "code": "001-47",
    "value": "17258529.00"
  },
  {
    "code": "001-30",
    "value": "660000.00"
  },
  {
    "code": "001-640",
    "value": "411381.00"
  },
  {
    "code": "001-63",
    "value": "777000.00"
  },
  {
    "code": "001-216",
    "value": "874705.00"
  },
  {
    "code": "001-471",
    "value": "10629372.00"
  },
  {
    "code": "001-196",
    "value": "0.00"
  },
  {
    "code": "001-477",
    "value": "1591300.00"
  },
  {
    "code": "001-483",
    "value": "3738420.00"
  },
  {
    "code": "001-202",
    "value": "0.00"
  },
  {
    "code": "001-191",
    "value": "1255000.00"
  },
  {
    "code": "001-272",
    "value": "0.00"
  },
  {
    "code": "001-189",
    "value": "0.00"
  },
  {
    "code": "001-704",
    "value": "118151.00"
  },
  {
    "code": "001-726",
    "value": "210000.00"
  },
  {
    "code": "001-366",
    "value": "34680.00"
  },
  {
    "code": "B005 - 2",
    "value": "33565000.00"
  },
  {
    "code": "001-76",
    "value": "4860000.00"
  },
  {
    "code": "001-481",
    "value": "285200.00"
  },
  {
    "code": "001-220",
    "value": "4671000.00"
  },
  {
    "code": "001-661",
    "value": "11976350.00"
  },
  {
    "code": "001-109",
    "value": "240000.00"
  },
  {
    "code": "002-6",
    "value": "1882825.00"
  },
  {
    "code": "001-411",
    "value": "382392.00"
  },
  {
    "code": "001-2",
    "value": "6422000.00"
  },
  {
    "code": "001-51",
    "value": "1725675.00"
  },
  {
    "code": "001-384",
    "value": "425000.00"
  },
  {
    "code": "001-13",
    "value": "22475.00"
  },
  {
    "code": "001-204",
    "value": "0.00"
  },
  {
    "code": "001-160",
    "value": "67000.00"
  },
  {
    "code": "001-685",
    "value": "33500.00"
  },
  {
    "code": "001-619",
    "value": "31292.00"
  },
  {
    "code": "001-716",
    "value": "30981.00"
  },
  {
    "code": "001-21",
    "value": "38363.00"
  },
  {
    "code": "001-678",
    "value": "380369.00"
  },
  {
    "code": "001-70",
    "value": "609550.00"
  },
  {
    "code": "001-644",
    "value": "629816.00"
  },
  {
    "code": "001-107",
    "value": "7872116.00"
  },
  {
    "code": "001-478",
    "value": "7752780.00"
  },
  {
    "code": "002-21",
    "value": "128737.00"
  },
  {
    "code": "001-111",
    "value": "172845.00"
  },
  {
    "code": "001-684",
    "value": "261039.00"
  },
  {
    "code": "001-122",
    "value": "215500.00"
  },
  {
    "code": "001-659",
    "value": "43100.00"
  },
  {
    "code": "001-648",
    "value": "115809.00"
  },
  {
    "code": "001-266",
    "value": "0.00"
  },
  {
    "code": "001-271",
    "value": "0.00"
  },
  {
    "code": "001-711",
    "value": "42500.00"
  },
  {
    "code": "001-60",
    "value": "3840000.00"
  },
  {
    "code": "001-50",
    "value": "308000.00"
  },
  {
    "code": "001-595",
    "value": "143500.00"
  },
  {
    "code": "001-245",
    "value": "465000.00"
  },
  {
    "code": "001-215",
    "value": "0.00"
  },
  {
    "code": "001-698",
    "value": "2360000.00"
  },
  {
    "code": "001-693",
    "value": "469300.00"
  },
  {
    "code": "001-187",
    "value": "0.00"
  },
  {
    "code": "001-17",
    "value": "5447090.00"
  },
  {
    "code": "001-291",
    "value": "31187612.00"
  },
  {
    "code": "001-301",
    "value": "138260.00"
  },
  {
    "code": "001-302",
    "value": "81780.00"
  },
  {
    "code": "s007",
    "value": "600000.00"
  },
  {
    "code": "s008",
    "value": "600000.00"
  },
  {
    "code": "S002",
    "value": "600000.00"
  },
  {
    "code": "001-724",
    "value": "601920.00"
  },
  {
    "code": "A001",
    "value": "4020000.00"
  },
  {
    "code": "A003 - M",
    "value": "170000.00"
  },
  {
    "code": "C002",
    "value": "640000.00"
  },
  {
    "code": "C002-1",
    "value": "31000.00"
  },
  {
    "code": "C001",
    "value": "310000.00"
  },
  {
    "code": "C001-1",
    "value": "82600.00"
  },
  {
    "code": "C006-1",
    "value": "124336.00"
  },
  {
    "code": "001-258",
    "value": "1346870.00"
  },
  {
    "code": "001-259",
    "value": "134110.00"
  },
  {
    "code": "001-260",
    "value": "370130.00"
  },
  {
    "code": "001-308",
    "value": "58680.00"
  },
  {
    "code": "B008",
    "value": "14586500.00"
  },
  {
    "code": "B008 - N",
    "value": "2492500.00"
  },
  {
    "code": "B015",
    "value": "17149250.00"
  },
  {
    "code": "B015 - N",
    "value": "2341500.00"
  },
  {
    "code": "B008 - H",
    "value": "4531500.00"
  },
  {
    "code": "B008 - HL",
    "value": "35097750.00"
  },
  {
    "code": "B003 - E",
    "value": "328680.00"
  },
  {
    "code": "B001",
    "value": "86224175.00"
  },
  {
    "code": "B001 - E",
    "value": "1502300.00"
  },
  {
    "code": "B001 - EN",
    "value": "36312500.00"
  },
  {
    "code": "B001 - H",
    "value": "22542750.00"
  },
  {
    "code": "B001 - HL",
    "value": "11244500.00"
  },
  {
    "code": "B001 - N",
    "value": "6300000.00"
  },
  {
    "code": "R016",
    "value": "348266.00"
  },
  {
    "code": "R010",
    "value": "145000.00"
  },
  {
    "code": "R010-M",
    "value": "145000.00"
  },
  {
    "code": "R010-M01",
    "value": "191730.00"
  },
  {
    "code": "R010-MD",
    "value": "290000.00"
  },
  {
    "code": "R010-MD-1",
    "value": "61770.00"
  },
  {
    "code": "R013",
    "value": "435000.00"
  },
  {
    "code": "R013-M",
    "value": "580000.00"
  },
  {
    "code": "R013-MD",
    "value": "435000.00"
  },
  {
    "code": "R013-MD-1",
    "value": "2320.00"
  },
  {
    "code": "R013-M01",
    "value": "64380.00"
  },
  {
    "code": "R017",
    "value": "1690000.00"
  },
  {
    "code": "R017 - 1",
    "value": "164860.00"
  },
  {
    "code": "001-168",
    "value": "1454120.00"
  },
  {
    "code": "001-172",
    "value": "466400.00"
  },
  {
    "code": "001-164",
    "value": "2044953.00"
  },
  {
    "code": "001-167",
    "value": "1603062.00"
  },
  {
    "code": "001-287",
    "value": "110000.00"
  },
  {
    "code": "001-169",
    "value": "530400.00"
  },
  {
    "code": "001-173",
    "value": "442000.00"
  },
  {
    "code": "001-714",
    "value": "15265.00"
  },
  {
    "code": "001-715",
    "value": "4349.20"
  },
  {
    "code": "001-713",
    "value": "14896.00"
  },
  {
    "code": "GBSS001",
    "value": "185980.00"
  },
  {
    "code": "GBSS016",
    "value": "16808550.00"
  },
  {
    "code": "GBSS016S",
    "value": "186558.00"
  },
  {
    "code": "GBSR002",
    "value": "78400.00"
  },
  {
    "code": "GBSR004",
    "value": "367200.00"
  },
  {
    "code": "GBSR010A",
    "value": "57218.00"
  },
  {
    "code": "GBSR010",
    "value": "76304.00"
  },
  {
    "code": "S016-GBX",
    "value": "105452130.00"
  },
  {
    "code": "001-10",
    "value": "52500.00"
  },
  {
    "code": "001-184",
    "value": "0.00"
  },
  {
    "code": "001-636",
    "value": "43316.00"
  },
  {
    "code": "001-637",
    "value": "54145.00"
  },
  {
    "code": "001-638",
    "value": "0.00"
  },
  {
    "code": "001-645",
    "value": "36906.00"
  },
  {
    "code": "002-20",
    "value": "356000.00"
  },
  {
    "code": "001-162",
    "value": "0.00"
  },
  {
    "code": "001-235",
    "value": "307400.00"
  },
  {
    "code": "001-123",
    "value": "198376.00"
  },
  {
    "code": "001-620",
    "value": "3500000.00"
  },
  {
    "code": "001-350",
    "value": "728000.00"
  },
  {
    "code": "001-240",
    "value": "1261112.00"
  },
  {
    "code": "001-470",
    "value": "5026500.00"
  },
  {
    "code": "001-83",
    "value": "8493100.00"
  },
  {
    "code": "001-273",
    "value": "0.00"
  },
  {
    "code": "001-200",
    "value": "0.00"
  },
  {
    "code": "001-199",
    "value": "877485.00"
  },
  {
    "code": "001-149",
    "value": "0.00"
  },
  {
    "code": "001-40",
    "value": "875000.00"
  },
  {
    "code": "001-686",
    "value": "166500.00"
  },
  {
    "code": "001-687",
    "value": "261039.00"
  },
  {
    "code": "001-41",
    "value": "350000.00"
  },
  {
    "code": "001-42",
    "value": "590000.00"
  },
  {
    "code": "001-695",
    "value": "690300.00"
  },
  {
    "code": "001-690",
    "value": "690300.00"
  },
  {
    "code": "001-694",
    "value": "690300.00"
  },
  {
    "code": "001-268",
    "value": "0.00"
  },
  {
    "code": "001-267",
    "value": "0.00"
  },
  {
    "code": "001-712",
    "value": "81600.00"
  },
  {
    "code": "001-131",
    "value": "3280000.00"
  },
  {
    "code": "001-145",
    "value": "2650100.00"
  },
  {
    "code": "001-649",
    "value": "23221.00"
  },
  {
    "code": "001-198",
    "value": "0.00"
  },
  {
    "code": "001-441",
    "value": "309300.00"
  },
  {
    "code": "001-590",
    "value": "500000.00"
  },
  {
    "code": "001-43",
    "value": "330000.00"
  },
  {
    "code": "001-9",
    "value": "0.00"
  },
  {
    "code": "001-691",
    "value": "165000.00"
  },
  {
    "code": "001-183",
    "value": "0.00"
  },
  {
    "code": "001-616",
    "value": "189200.00"
  },
  {
    "code": "001-147",
    "value": "137600.00"
  },
  {
    "code": "001-274",
    "value": "0.00"
  },
  {
    "code": "001-679",
    "value": "2566667.00"
  },
  {
    "code": "001-435",
    "value": "666665.00"
  },
  {
    "code": "001-717",
    "value": "127414.00"
  },
  {
    "code": "001-436",
    "value": "77520.00"
  },
  {
    "code": "001-697",
    "value": "130000.00"
  },
  {
    "code": "001-660",
    "value": "0.00"
  },
  {
    "code": "001-188",
    "value": "79320.00"
  },
  {
    "code": "001-475",
    "value": "79320.00"
  },
  {
    "code": "001-201",
    "value": "62400.00"
  },
  {
    "code": "001-389",
    "value": "27192.00"
  },
  {
    "code": "001-362",
    "value": "3420.00"
  },
  {
    "code": "002-7",
    "value": "105000.00"
  },
  {
    "code": "001-69",
    "value": "118372.00"
  },
  {
    "code": "001-218",
    "value": "0.00"
  },
  {
    "code": "001-249",
    "value": "187200.00"
  },
  {
    "code": "001-161",
    "value": "5415.00"
  },
  {
    "code": "001-112",
    "value": "420000.00"
  },
  {
    "code": "001-62",
    "value": "1600000.00"
  },
  {
    "code": "001-39",
    "value": "19243630.00"
  },
  {
    "code": "001-190",
    "value": "0.00"
  },
  {
    "code": "001-434",
    "value": "51920.00"
  },
  {
    "code": "001-136",
    "value": "30996.00"
  },
  {
    "code": "001-701",
    "value": "28994.00"
  },
  {
    "code": "001-100",
    "value": "1287832.00"
  },
  {
    "code": "001-591",
    "value": "660820.00"
  },
  {
    "code": "001-462",
    "value": "258165.00"
  },
  {
    "code": "001-461",
    "value": "319950.00"
  },
  {
    "code": "001-723",
    "value": "211200.00"
  },
  {
    "code": "001-721",
    "value": "417120.00"
  },
  {
    "code": "001-719",
    "value": "89760.00"
  },
  {
    "code": "001-706",
    "value": "70500.00"
  },
  {
    "code": "001-722",
    "value": "512736.00"
  },
  {
    "code": "001-720",
    "value": "1015008.00"
  },
  {
    "code": "001-718",
    "value": "198816.00"
  },
  {
    "code": "001-705",
    "value": "69370.00"
  },
  {
    "code": "001-16",
    "value": "560000.00"
  },
  {
    "code": "001-186",
    "value": "0.00"
  },
  {
    "code": "001-74",
    "value": "199500.00"
  },
  {
    "code": "001-19",
    "value": "2025000.00"
  },
  {
    "code": "001-658",
    "value": "151823.00"
  },
  {
    "code": "001-197",
    "value": "0.00"
  },
  {
    "code": "001-79",
    "value": "5344900.00"
  },
  {
    "code": "001-158",
    "value": "0.00"
  },
  {
    "code": "001-219",
    "value": "3075000.00"
  },
  {
    "code": "001-488",
    "value": "36600.00"
  },
  {
    "code": "001-13.1",
    "value": "85211.00"
  },
  {
    "code": "001-114",
    "value": "437400.00"
  },
  {
    "code": "001-642",
    "value": "274196.00"
  },
  {
    "code": "001-445",
    "value": "245000.00"
  },
  {
    "code": "001-692",
    "value": "72900.00"
  },
  {
    "code": "002-19",
    "value": "88676.00"
  },
  {
    "code": "001-117",
    "value": "20000.00"
  },
  {
    "code": "001-207",
    "value": "10000.00"
  },
  {
    "code": "001-208",
    "value": "0.00"
  },
  {
    "code": "001-328",
    "value": "5747800.00"
  },
  {
    "code": "001-206",
    "value": "0.00"
  },
  {
    "code": "001-71",
    "value": "126954.00"
  },
  {
    "code": "TP-7.21m",
    "value": "40000.00"
  },
  {
    "code": "TP-7.1",
    "value": "28800.00"
  },
  {
    "code": "001-343",
    "value": "450000.00"
  },
  {
    "code": "001-92",
    "value": "0.00"
  },
  {
    "code": "001-91",
    "value": "253600.00"
  },
  {
    "code": "001-677",
    "value": "43100.00"
  },
  {
    "code": "001-360",
    "value": "198167.00"
  },
  {
    "code": "001-696",
    "value": "2564.00"
  },
  {
    "code": "001-689",
    "value": "1282.00"
  },
  {
    "code": "001-115",
    "value": "1794890.00"
  },
  {
    "code": "001-78",
    "value": "28876930.00"
  },
  {
    "code": "001-49",
    "value": "4383000.00"
  },
  {
    "code": "001-153",
    "value": "605000.00"
  },
  {
    "code": "B002 - 1",
    "value": "88450.00"
  },
  {
    "code": "001-88",
    "value": "568850.00"
  },
  {
    "code": "001-37",
    "value": "249600.00"
  },
  {
    "code": "001-4",
    "value": "2814975.00"
  },
  {
    "code": "001-127",
    "value": "5779400.00"
  },
  {
    "code": "R003-P",
    "value": "1473120.00"
  },
  {
    "code": "S00GBX",
    "value": "47000000.00"
  },
  {
    "code": "001-66",
    "value": "19050948.00"
  },
  {
    "code": "001-7",
    "value": "23548840.00"
  },
  {
    "code": "R008",
    "value": "4223912.00"
  },
  {
    "code": "001-171",
    "value": "200500.00"
  },
  {
    "code": "R00GbX",
    "value": "6650000.00"
  },
  {
    "code": "001-234",
    "value": "116820.00"
  },
  {
    "code": "S014",
    "value": "190000.00"
  },
  {
    "code": "001-304",
    "value": "420900.00"
  },
  {
    "code": "001-336",
    "value": "79500.00"
  },
  {
    "code": "S017",
    "value": "276000.00"
  },
  {
    "code": "C003",
    "value": "1020000.00"
  },
  {
    "code": "C003-1",
    "value": "12155.00"
  },
  {
    "code": "001-428",
    "value": "42300.00"
  },
  {
    "code": "S012-L",
    "value": "245000.00"
  },
  {
    "code": "001-455",
    "value": "13860.00"
  },
  {
    "code": "A002-L1",
    "value": "103240.00"
  },
  {
    "code": "A002-L",
    "value": "440000.00"
  },
  {
    "code": "S013-L",
    "value": "580000.00"
  },
  {
    "code": "S013-L1",
    "value": "171950.00"
  },
  {
    "code": "C001 - FCM 1",
    "value": "360000.00"
  },
  {
    "code": "C001 - FCM 2",
    "value": "61440.00"
  },
  {
    "code": "A002-LM",
    "value": "1980000.00"
  },
  {
    "code": "A002-LM1",
    "value": "276832.00"
  },
  {
    "code": "001-646",
    "value": "167320.00"
  },
  {
    "code": "001-622",
    "value": "14611637.00"
  },
  {
    "code": "001-623",
    "value": "7961585.00"
  },
  {
    "code": "001-624",
    "value": "5977275.00"
  },
  {
    "code": "001-632",
    "value": "1056688.00"
  },
  {
    "code": "001-702",
    "value": "29736.00"
  },
  {
    "code": "001-626",
    "value": "18693700.00"
  },
  {
    "code": "001-625",
    "value": "0.00"
  },
  {
    "code": "001-627",
    "value": "820890.00"
  },
  {
    "code": "001-628",
    "value": "4688496.00"
  },
  {
    "code": "001-651",
    "value": "665896.00"
  },
  {
    "code": "001-652",
    "value": "258240.00"
  },
  {
    "code": "001-653",
    "value": "383296.00"
  },
  {
    "code": "001-654",
    "value": "238770.00"
  },
  {
    "code": "001-655",
    "value": "228480.00"
  },
  {
    "code": "001-656",
    "value": "90576.00"
  },
  {
    "code": "001-177",
    "value": "414200.00"
  },
  {
    "code": "001-72",
    "value": "7500000.00"
  },
  {
    "code": "001-85",
    "value": "1633275.00"
  },
  {
    "code": "001-82",
    "value": "701230.00"
  },
  {
    "code": "001-36",
    "value": "43218000.00"
  },
  {
    "code": "B005 - 1",
    "value": "4960160.00"
  },
  {
    "code": "001-323",
    "value": "73843250.00"
  },
  {
    "code": "002-9",
    "value": "30000.00"
  },
  {
    "code": "001-119",
    "value": "289920.00"
  },
  {
    "code": "001-193",
    "value": "25000.00"
  },
  {
    "code": "001-46",
    "value": "228600000.00"
  },
  {
    "code": "001-154",
    "value": "330000.00"
  },
  {
    "code": "001-29",
    "value": "3000000.00"
  },
  {
    "code": "001-596",
    "value": "1876902.00"
  },
  {
    "code": "001-56",
    "value": "506781.00"
  },
  {
    "code": "001-32",
    "value": "8175957.00"
  },
  {
    "code": "001-322",
    "value": "5875740.00"
  },
  {
    "code": "001-217",
    "value": "893750.00"
  },
  {
    "code": "001-238",
    "value": "182800.00"
  },
  {
    "code": "001-59",
    "value": "542036.00"
  },
  {
    "code": "001-128",
    "value": "310500.00"
  },
  {
    "code": "001-102",
    "value": "208500.00"
  },
  {
    "code": "001-424",
    "value": "224998.00"
  },
  {
    "code": "001-64",
    "value": "9625150.00"
  },
  {
    "code": "001-120",
    "value": "3400000.00"
  },
  {
    "code": "001-80",
    "value": "122600.00"
  },
  {
    "code": "R002",
    "value": "966700.00"
  },
  {
    "code": "R001",
    "value": "3615220.00"
  },
  {
    "code": "001-67",
    "value": "6200620.00"
  },
  {
    "code": "R-003",
    "value": "27900.00"
  },
  {
    "code": "R012",
    "value": "7191000.00"
  },
  {
    "code": "B003",
    "value": "957000.00"
  },
  {
    "code": "B002",
    "value": "56925.00"
  },
  {
    "code": "B005",
    "value": "526635.00"
  },
  {
    "code": "001-87",
    "value": "340000.00"
  },
  {
    "code": "B003 - N",
    "value": "56500.00"
  },
  {
    "code": "001-365",
    "value": "1500000.00"
  },
  {
    "code": "001-241",
    "value": "880000.00"
  },
  {
    "code": "001-159",
    "value": "0.00"
  },
  {
    "code": "001-55",
    "value": "3570000.00"
  },
  {
    "code": "002-18",
    "value": "46500.00"
  },
  {
    "code": "001-242",
    "value": "420000.00"
  },
  {
    "code": "001-364",
    "value": "622132.00"
  },
  {
    "code": "001-65",
    "value": "42000.00"
  },
  {
    "code": "001-139",
    "value": "178398.00"
  },
  {
    "code": "001-205",
    "value": "0.00"
  },
  {
    "code": "001-20",
    "value": "0.00"
  },
  {
    "code": "R003 - 99",
    "value": "0.00"
  },
  {
    "code": "001-152",
    "value": "6787325.00"
  },
  {
    "code": "B008 - M",
    "value": "3150000.00"
  },
  {
    "code": "B011",
    "value": "9083500.00"
  },
  {
    "code": "B014",
    "value": "0.00"
  },
  {
    "code": "001-707",
    "value": "100145.00"
  },
  {
    "code": "001-484",
    "value": "1184735.00"
  },
  {
    "code": "001-12.1",
    "value": "387000.00"
  },
  {
    "code": "001-246",
    "value": "500000.00"
  },
  {
    "code": "R010 - GB",
    "value": "3060000.00"
  },
  {
    "code": "R003",
    "value": "43560.00"
  },
  {
    "code": "001-11.1",
    "value": "65600.00"
  },
  {
    "code": "001-237",
    "value": "172000.00"
  },
  {
    "code": "001-239",
    "value": "175000.00"
  },
  {
    "code": "001-179",
    "value": "64020.00"
  },
  {
    "code": "001-303",
    "value": "156000.00"
  },
  {
    "code": "001-175",
    "value": "942000.00"
  },
  {
    "code": "001-178",
    "value": "310000.00"
  },
  {
    "code": "001-182",
    "value": "194000.00"
  },
  {
    "code": "001-221",
    "value": "110000.00"
  },
  {
    "code": "001-306",
    "value": "302000.00"
  },
  {
    "code": "001-176",
    "value": "1177500.00"
  },
  {
    "code": "001-174",
    "value": "1413000.00"
  },
  {
    "code": "001-181",
    "value": "420000.00"
  },
  {
    "code": "001-170",
    "value": "1064266.00"
  },
  {
    "code": "001-180",
    "value": "460950.00"
  },
  {
    "code": "001-165",
    "value": "1895400.00"
  },
  {
    "code": "R019 - GB",
    "value": "1215000.00"
  },
  {
    "code": "001-573",
    "value": "15564420.00"
  },
  {
    "code": "001-377",
    "value": "56775.00"
  },
  {
    "code": "001-476",
    "value": "3169500.00"
  },
  {
    "code": "001-26",
    "value": "123190.00"
  },
  {
    "code": "001-324",
    "value": "179172.00"
  },
  {
    "code": "001-209",
    "value": "0.00"
  },
  {
    "code": "001-650",
    "value": "61500.00"
  },
  {
    "code": "001-359",
    "value": "2268000.00"
  },
  {
    "code": "001-383",
    "value": "27000.00"
  },
  {
    "code": "001-357",
    "value": "725810.00"
  },
  {
    "code": "001-358",
    "value": "713830.00"
  },
  {
    "code": "001-380",
    "value": "75500.00"
  },
  {
    "code": "001-113",
    "value": "469300.00"
  },
  {
    "code": "001-597",
    "value": "243100.00"
  },
  {
    "code": "001-634",
    "value": "96720.00"
  },
  {
    "code": "001-635",
    "value": "119467.00"
  },
  {
    "code": "001-376",
    "value": "68276.00"
  },
  {
    "code": "001-586",
    "value": "144900.00"
  },
  {
    "code": "001-375",
    "value": "115775.00"
  },
  {
    "code": "001-255",
    "value": "4500000.00"
  },
  {
    "code": "001 - 156",
    "value": "3100000.00"
  },
  {
    "code": "001-379",
    "value": "103000.00"
  },
  {
    "code": "001-378",
    "value": "28275.00"
  },
  {
    "code": "001-270",
    "value": "0.00"
  },
  {
    "code": "001-340",
    "value": "108546.00"
  },
  {
    "code": "001-482",
    "value": "57792.00"
  },
  {
    "code": "001-86",
    "value": "395000.00"
  },
  {
    "code": "001-68",
    "value": "131600.00"
  },
  {
    "code": "001-195",
    "value": "0.00"
  },
  {
    "code": "001-282",
    "value": "30000.00"
  },
  {
    "code": "001-284",
    "value": "32000.00"
  },
  {
    "code": "001-299",
    "value": "20000.00"
  },
  {
    "code": "001-345",
    "value": "198800.00"
  },
  {
    "code": "001-356",
    "value": "4000000.00"
  },
  {
    "code": "001-390",
    "value": "89500.00"
  },
  {
    "code": "001-391",
    "value": "166700.00"
  },
  {
    "code": "001-392",
    "value": "600000.00"
  },
  {
    "code": "001-394",
    "value": "630000.00"
  },
  {
    "code": "001-439",
    "value": "22000.00"
  },
  {
    "code": "001-444",
    "value": "1034000.00"
  },
  {
    "code": "001-453",
    "value": "10000.00"
  },
  {
    "code": "001-454",
    "value": "21000.00"
  },
  {
    "code": "001-458",
    "value": "215000.00"
  },
  {
    "code": "001-269",
    "value": "0.00"
  },
  {
    "code": "001-351",
    "value": "244944.00"
  },
  {
    "code": "001-412",
    "value": "2900000.00"
  },
  {
    "code": "001-58",
    "value": "1134000.00"
  },
  {
    "code": "001-406",
    "value": "207000.00"
  },
  {
    "code": "001-75",
    "value": "17770.00"
  },
  {
    "code": "001-423",
    "value": "11282.00"
  },
  {
    "code": "001-327",
    "value": "63273.00"
  },
  {
    "code": "001-43",
    "value": "220000.00"
  },
  {
    "code": "001-63",
    "value": "621000.00"
  },
  {
    "code": "001-147",
    "value": "688000.00"
  },
  {
    "code": "001-212",
    "value": "12100.00"
  },
  {
    "code": "001-213",
    "value": "109836.00"
  },
  {
    "code": "001-343",
    "value": "900000.00"
  },
  {
    "code": "001-445",
    "value": "245000.00"
  },
  {
    "code": "001-485",
    "value": "175800.00"
  },
  {
    "code": "001-588",
    "value": "346000.00"
  },
  {
    "code": "001-593",
    "value": "142800.00"
  },
  {
    "code": "001-594",
    "value": "75400.00"
  },
  {
    "code": "001-619",
    "value": "133422.00"
  }
]';

        $data = json_decode($json, true);

        foreach ($data as $row) {
            $item = Item::where('code', $row['code'])->first();
            $value = str_replace(',', '', $row['value']);
            echo $row['code'] . ' => ' . $row['value'] . PHP_EOL;

            if ($item) {
                $inventory = Inventory::where('item_id', '=', $item->id)
                    ->where('form_date', '<', '2026-07-01')
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
                    
                    $l_inventory->total_value_all = $prevTotalVal + ($l_inventory->quantity * $l_inventory->price);
                    if (!$l_inventory->total_quantity_all || $l_inventory->total_quantity_all == 0) {
                        $l_inventory->cogs = 0;
                    } else {
                        $l_inventory->cogs = $l_inventory->total_value_all / $l_inventory->total_quantity_all;
                    }
                    $l_inventory->save();

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
                    $l_inventory->save();
                }
            }
        }
    }
}