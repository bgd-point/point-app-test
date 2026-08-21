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

class RecalculateCutoff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recalculate:cutoff';

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
    "code": "001-377",
    "value": 56775
  },
  {
    "code": "001-683",
    "value": 620000
  },
  {
    "code": "001-688",
    "value": 620000
  },
  {
    "code": "001-703",
    "value": 25411
  },
  {
    "code": "001-103",
    "value": 4950
  },
  {
    "code": "001-629",
    "value": 80767
  },
  {
    "code": "001-630",
    "value": 80767
  },
  {
    "code": "001-631",
    "value": 80767
  },
  {
    "code": "001-242",
    "value": 210000
  },
  {
    "code": "001-80",
    "value": 61300
  },
  {
    "code": "001-244",
    "value": 420000
  },
  {
    "code": "001-243",
    "value": 105000
  },
  {
    "code": "001-392",
    "value": 200000
  },
  {
    "code": "001-487",
    "value": 357134
  },
  {
    "code": "001-476",
    "value": 3169500
  },
  {
    "code": "001-110",
    "value": 25000
  },
  {
    "code": "001-236",
    "value": 22300
  },
  {
    "code": "001-77",
    "value": 944507.55
  },
  {
    "code": "001-26",
    "value": 1270
  },
  {
    "code": "001-324",
    "value": 3318
  },
  {
    "code": "001-47",
    "value": 5273
  },
  {
    "code": "001-209",
    "value": 0
  },
  {
    "code": "001-30",
    "value": 30000
  },
  {
    "code": "001-49",
    "value": 4500
  },
  {
    "code": "001-650",
    "value": 20500
  },
  {
    "code": "001-640",
    "value": 137127
  },
  {
    "code": "001-63",
    "value": 8441.86
  },
  {
    "code": "001-428",
    "value": 90
  },
  {
    "code": "001-216",
    "value": 7606.13
  },
  {
    "code": "001-471",
    "value": 12475.79
  },
  {
    "code": "001-351",
    "value": 2916
  },
  {
    "code": "001-356",
    "value": 4000000
  },
  {
    "code": "001-364",
    "value": 44438
  },
  {
    "code": "001-196",
    "value": 0
  },
  {
    "code": "001-68",
    "value": 131600
  },
  {
    "code": "001-477",
    "value": 1591300
  },
  {
    "code": "001-65",
    "value": 14000
  },
  {
    "code": "001-483",
    "value": 3738420
  },
  {
    "code": "001-153",
    "value": 605000
  },
  {
    "code": "001-202",
    "value": 0
  },
  {
    "code": "B002 - 1",
    "value": 1450
  },
  {
    "code": "001-191",
    "value": 627500
  },
  {
    "code": "001-272",
    "value": 0
  },
  {
    "code": "001-189",
    "value": 0
  },
  {
    "code": "001-704",
    "value": 10741
  },
  {
    "code": "001-726",
    "value": 210000
  },
  {
    "code": "001-86",
    "value": 197500
  },
  {
    "code": "001-366",
    "value": 578
  },
  {
    "code": "B005 - 2",
    "value": 2397500
  },
  {
    "code": "001-76",
    "value": 2430000
  },
  {
    "code": "001-481",
    "value": 285200
  },
  {
    "code": "001-220",
    "value": 1557000
  },
  {
    "code": "001-661",
    "value": 11976350
  },
  {
    "code": "001-359",
    "value": 2268000
  },
  {
    "code": "001-109",
    "value": 80000
  },
  {
    "code": "001-139",
    "value": 29733
  },
  {
    "code": "002-6",
    "value": 725
  },
  {
    "code": "001-411",
    "value": 15933
  },
  {
    "code": "001-2",
    "value": 3250
  },
  {
    "code": "001-51",
    "value": 1425
  },
  {
    "code": "001-88",
    "value": 251.15
  },
  {
    "code": "001-37",
    "value": 780
  },
  {
    "code": "001-384",
    "value": 17000
  },
  {
    "code": "001-383",
    "value": 27000
  },
  {
    "code": "001-357",
    "value": 1810
  },
  {
    "code": "001-358",
    "value": 2210
  },
  {
    "code": "001-13",
    "value": 155
  },
  {
    "code": "001-159",
    "value": 0
  },
  {
    "code": "001-204",
    "value": 0
  },
  {
    "code": "001-160",
    "value": 16750
  },
  {
    "code": "001-685",
    "value": 16750
  },
  {
    "code": "001-619",
    "value": 31292
  },
  {
    "code": "001-716",
    "value": 10327
  },
  {
    "code": "001-21",
    "value": 38363
  },
  {
    "code": "001-678",
    "value": 380369
  },
  {
    "code": "001-70",
    "value": 3650
  },
  {
    "code": "001-644",
    "value": 68
  },
  {
    "code": "001-107",
    "value": 1312019.33
  },
  {
    "code": "001-478",
    "value": 7752780
  },
  {
    "code": "001-20",
    "value": 0
  },
  {
    "code": "001-237",
    "value": 3440
  },
  {
    "code": "001-239",
    "value": 3500
  },
  {
    "code": "002-21",
    "value": 128737
  },
  {
    "code": "001-444",
    "value": 1034000
  },
  {
    "code": "001-111",
    "value": 3841
  },
  {
    "code": "001-684",
    "value": 261039
  },
  {
    "code": "001-122",
    "value": 43100
  },
  {
    "code": "001-659",
    "value": 43100
  },
  {
    "code": "001-648",
    "value": 38603
  },
  {
    "code": "001-266",
    "value": 0
  },
  {
    "code": "001-271",
    "value": 0
  },
  {
    "code": "001-711",
    "value": 1700
  },
  {
    "code": "001-709",
    "value": 29000
  },
  {
    "code": "001-60",
    "value": 120000
  },
  {
    "code": "001-50",
    "value": 28000
  },
  {
    "code": "001-596",
    "value": 110406
  },
  {
    "code": "001-380",
    "value": 37750
  },
  {
    "code": "001-595",
    "value": 71750
  },
  {
    "code": "001-245",
    "value": 232500
  },
  {
    "code": "001-215",
    "value": 0
  },
  {
    "code": "001-406",
    "value": 34500
  },
  {
    "code": "001-56",
    "value": 33785.4
  },
  {
    "code": "001-55",
    "value": 85000
  },
  {
    "code": "001-4",
    "value": 4771.14
  },
  {
    "code": "001-127",
    "value": 7100
  },
  {
    "code": "001-626",
    "value": 4646.99
  },
  {
    "code": "001-32",
    "value": 100937.74
  },
  {
    "code": "001-698",
    "value": 5000
  },
  {
    "code": "001-113",
    "value": 469300
  },
  {
    "code": "001-693",
    "value": 469300
  },
  {
    "code": "001-187",
    "value": 0
  },
  {
    "code": "001-17",
    "value": 272354.5
  },
  {
    "code": "001-291",
    "value": 331783.11
  },
  {
    "code": "B015 - H",
    "value": 21500
  },
  {
    "code": "R003",
    "value": 495
  },
  {
    "code": "R003 - 99",
    "value": 0
  },
  {
    "code": "R002",
    "value": 175
  },
  {
    "code": "R001",
    "value": 4340
  },
  {
    "code": "001-301",
    "value": 155
  },
  {
    "code": "001-302",
    "value": 145
  },
  {
    "code": "S001",
    "value": 200000
  },
  {
    "code": "S003",
    "value": 200000
  },
  {
    "code": "S013-L",
    "value": 145000
  },
  {
    "code": "S013-L1",
    "value": 181
  },
  {
    "code": "S014",
    "value": 95000
  },
  {
    "code": "S004",
    "value": 200000
  },
  {
    "code": "s007",
    "value": 200000
  },
  {
    "code": "S002",
    "value": 200000
  },
  {
    "code": "S017",
    "value": 138000
  },
  {
    "code": "R003-P",
    "value": 620
  },
  {
    "code": "001-724",
    "value": 120
  },
  {
    "code": "S00GBX",
    "value": 94000
  },
  {
    "code": "R010 - GB",
    "value": 34000
  },
  {
    "code": "R019 - GB",
    "value": 27000
  },
  {
    "code": "A002",
    "value": 163000
  },
  {
    "code": "A001",
    "value": 167500
  },
  {
    "code": "A002-L",
    "value": 110000
  },
  {
    "code": "A002-L1",
    "value": 116
  },
  {
    "code": "A002-LM",
    "value": 110000
  },
  {
    "code": "A002-LM1",
    "value": 328
  },
  {
    "code": "A003 - M",
    "value": 170000
  },
  {
    "code": "C002",
    "value": 160000
  },
  {
    "code": "C002-1",
    "value": 124
  },
  {
    "code": "C001",
    "value": 155000
  },
  {
    "code": "C001-1",
    "value": 118
  },
  {
    "code": "C003",
    "value": 85000
  },
  {
    "code": "C003-1",
    "value": 85
  },
  {
    "code": "C006-1",
    "value": 152
  },
  {
    "code": "C006",
    "value": 152000
  },
  {
    "code": "001-176",
    "value": 471
  },
  {
    "code": "001-66",
    "value": 252
  },
  {
    "code": "001-152",
    "value": 415
  },
  {
    "code": "001-322",
    "value": 465
  },
  {
    "code": "001-67",
    "value": 1370
  },
  {
    "code": "001-7",
    "value": 4340
  },
  {
    "code": "R008",
    "value": 50284.67
  },
  {
    "code": "R-003",
    "value": 620
  },
  {
    "code": "R012",
    "value": 47000
  },
  {
    "code": "001-171",
    "value": 29.93
  },
  {
    "code": "001-221",
    "value": 55
  },
  {
    "code": "001-452",
    "value": 77
  },
  {
    "code": "001-170",
    "value": 266
  },
  {
    "code": "001-258",
    "value": 549.6
  },
  {
    "code": "001-259",
    "value": 79.75
  },
  {
    "code": "001-260",
    "value": 123.67
  },
  {
    "code": "001-308",
    "value": 180
  },
  {
    "code": "B008",
    "value": 43251.57
  },
  {
    "code": "B008 - N",
    "value": 55045.45
  },
  {
    "code": "B015",
    "value": 27575
  },
  {
    "code": "B015 - N",
    "value": 27875
  },
  {
    "code": "B008 - M",
    "value": 35000
  },
  {
    "code": "B011",
    "value": 18500
  },
  {
    "code": "B008 - H",
    "value": 42750
  },
  {
    "code": "B008 - HL",
    "value": 42750
  },
  {
    "code": "B003",
    "value": 3552.36
  },
  {
    "code": "B003 - E",
    "value": 3320
  },
  {
    "code": "B003 - N",
    "value": 5650
  },
  {
    "code": "B001",
    "value": 8869.94
  },
  {
    "code": "B001 - E",
    "value": 8300
  },
  {
    "code": "B001 - EN",
    "value": 8300
  },
  {
    "code": "B001 - H",
    "value": 10750
  },
  {
    "code": "B001 - HL",
    "value": 10750
  },
  {
    "code": "B001 - N",
    "value": 14000
  },
  {
    "code": "B002",
    "value": 495
  },
  {
    "code": "B005",
    "value": 415
  },
  {
    "code": "R016",
    "value": 174133
  },
  {
    "code": "R010",
    "value": 145000
  },
  {
    "code": "R010-M",
    "value": 145000
  },
  {
    "code": "R010-M01",
    "value": 97.49
  },
  {
    "code": "R010-MD",
    "value": 145000
  },
  {
    "code": "R010-MD-1",
    "value": 145
  },
  {
    "code": "R00GbX",
    "value": 66500
  },
  {
    "code": "R013",
    "value": 145000
  },
  {
    "code": "R013-M",
    "value": 145000
  },
  {
    "code": "R013-MD",
    "value": 145000
  },
  {
    "code": "R013-MD-1",
    "value": 145
  },
  {
    "code": "R013-M01",
    "value": 145
  },
  {
    "code": "R017",
    "value": 130000
  },
  {
    "code": "R017 - 1",
    "value": 110.51
  },
  {
    "code": "001-179",
    "value": 110
  },
  {
    "code": "001-225",
    "value": 127
  },
  {
    "code": "001-168",
    "value": 174.11
  },
  {
    "code": "001-181",
    "value": 105
  },
  {
    "code": "001-172",
    "value": 147.42
  },
  {
    "code": "001-282",
    "value": 30000
  },
  {
    "code": "001-454",
    "value": 21000
  },
  {
    "code": "001-164",
    "value": 241.73
  },
  {
    "code": "001-178",
    "value": 167.97
  },
  {
    "code": "001-284",
    "value": 32000
  },
  {
    "code": "001-167",
    "value": 140.18
  },
  {
    "code": "001-177",
    "value": 138.07
  },
  {
    "code": "001-180",
    "value": 105
  },
  {
    "code": "001-287",
    "value": 36666.67
  },
  {
    "code": "001-169",
    "value": 174.01
  },
  {
    "code": "001-182",
    "value": 97
  },
  {
    "code": "001-173",
    "value": 149.26
  },
  {
    "code": "001-165",
    "value": 243
  },
  {
    "code": "001-174",
    "value": 471
  },
  {
    "code": "001-175",
    "value": 471
  },
  {
    "code": "001-234",
    "value": 198
  },
  {
    "code": "001-303",
    "value": 156
  },
  {
    "code": "001-299",
    "value": 20000
  },
  {
    "code": "001-453",
    "value": 10000
  },
  {
    "code": "001-304",
    "value": 143.16
  },
  {
    "code": "001-306",
    "value": 151
  },
  {
    "code": "001-336",
    "value": 154.58
  },
  {
    "code": "001-714",
    "value": 43
  },
  {
    "code": "001-715",
    "value": 33.2
  },
  {
    "code": "001-713",
    "value": 56
  },
  {
    "code": "GBSS001",
    "value": 170
  },
  {
    "code": "GBSS016",
    "value": 167
  },
  {
    "code": "GBSS016S",
    "value": 177
  },
  {
    "code": "GBSR002",
    "value": 98
  },
  {
    "code": "GBSR004",
    "value": 68
  },
  {
    "code": "GBSR010A",
    "value": 67
  },
  {
    "code": "GBSR010",
    "value": 76
  },
  {
    "code": "S012-L",
    "value": 245000
  },
  {
    "code": "S016-GBX",
    "value": 105
  },
  {
    "code": "C001 - FCM 2",
    "value": 120
  },
  {
    "code": "C001 - FCM 1",
    "value": 120000
  },
  {
    "code": "001-10",
    "value": 2100
  },
  {
    "code": "001-597",
    "value": 243100
  },
  {
    "code": "001-184",
    "value": 0
  },
  {
    "code": "001-646",
    "value": 356
  },
  {
    "code": "001-627",
    "value": 222
  },
  {
    "code": "001-628",
    "value": 755.21
  },
  {
    "code": "001-623",
    "value": 149.35
  },
  {
    "code": "001-622",
    "value": 304.56
  },
  {
    "code": "001-624",
    "value": 341.48
  },
  {
    "code": "001-634",
    "value": 104
  },
  {
    "code": "001-635",
    "value": 193
  },
  {
    "code": "001-702",
    "value": 357
  },
  {
    "code": "001-632",
    "value": 416
  },
  {
    "code": "001-636",
    "value": 238
  },
  {
    "code": "001-637",
    "value": 236
  },
  {
    "code": "001-638",
    "value": 0
  },
  {
    "code": "001-654",
    "value": 315
  },
  {
    "code": "001-653",
    "value": 852
  },
  {
    "code": "001-652",
    "value": 142
  },
  {
    "code": "001-651",
    "value": 379
  },
  {
    "code": "001-656",
    "value": 204
  },
  {
    "code": "001-655",
    "value": 816
  },
  {
    "code": "001-645",
    "value": 7097
  },
  {
    "code": "002-20",
    "value": 356000
  },
  {
    "code": "001-162",
    "value": 0
  },
  {
    "code": "001-235",
    "value": 307400
  },
  {
    "code": "001-123",
    "value": 198376
  },
  {
    "code": "001-620",
    "value": 3500000
  },
  {
    "code": "001-376",
    "value": 34138
  },
  {
    "code": "001-217",
    "value": 8125
  },
  {
    "code": "001-586",
    "value": 144900
  },
  {
    "code": "001-238",
    "value": 91400
  },
  {
    "code": "001-75",
    "value": 8885
  },
  {
    "code": "001-350",
    "value": 8000
  },
  {
    "code": "001-240",
    "value": 420370.67
  },
  {
    "code": "001-72",
    "value": 2500000
  },
  {
    "code": "001-390",
    "value": 89500
  },
  {
    "code": "001-412",
    "value": 2900000
  },
  {
    "code": "001-470",
    "value": 5026500
  },
  {
    "code": "001-83",
    "value": 707758.33
  },
  {
    "code": "001-273",
    "value": 0
  },
  {
    "code": "001-200",
    "value": 0
  },
  {
    "code": "001-199",
    "value": 877485
  },
  {
    "code": "001-458",
    "value": 215000
  },
  {
    "code": "001-149",
    "value": 0
  },
  {
    "code": "001-40",
    "value": 875000
  },
  {
    "code": "001-686",
    "value": 166500
  },
  {
    "code": "001-687",
    "value": 261039
  },
  {
    "code": "001-41",
    "value": 350000
  },
  {
    "code": "001-42",
    "value": 590000
  },
  {
    "code": "001-58",
    "value": 18000
  },
  {
    "code": "001-695",
    "value": 690300
  },
  {
    "code": "001-690",
    "value": 690300
  },
  {
    "code": "001-694",
    "value": 690300
  },
  {
    "code": "001-268",
    "value": 0
  },
  {
    "code": "001-267",
    "value": 0
  },
  {
    "code": "001-365",
    "value": 1500000
  },
  {
    "code": "001-375",
    "value": 115775
  },
  {
    "code": "001-85",
    "value": 4575
  },
  {
    "code": "001-712",
    "value": 136
  },
  {
    "code": "001-423",
    "value": 11282
  },
  {
    "code": "001-131",
    "value": 131200
  },
  {
    "code": "001-145",
    "value": 73613.89
  },
  {
    "code": "001-649",
    "value": 23221
  },
  {
    "code": "001-198",
    "value": 0
  },
  {
    "code": "001-441",
    "value": 309300
  },
  {
    "code": "001-82",
    "value": 2269.35
  },
  {
    "code": "001-590",
    "value": 500000
  },
  {
    "code": "001-43",
    "value": 110000
  },
  {
    "code": "001-9",
    "value": 0
  },
  {
    "code": "001-36",
    "value": 75
  },
  {
    "code": "B005 - 1",
    "value": 116
  },
  {
    "code": "001-323",
    "value": 87.67
  },
  {
    "code": "001-255",
    "value": 1500000
  },
  {
    "code": "001-691",
    "value": 165000
  },
  {
    "code": "001-183",
    "value": 0
  },
  {
    "code": "001-616",
    "value": 189200
  },
  {
    "code": "001-147",
    "value": 137600
  },
  {
    "code": "001-707",
    "value": 100145
  },
  {
    "code": "002-18",
    "value": 46500
  },
  {
    "code": "001-274",
    "value": 0
  },
  {
    "code": "B014",
    "value": 0
  },
  {
    "code": "001-484",
    "value": 236947
  },
  {
    "code": "001 - 156",
    "value": 3100000
  },
  {
    "code": "001-679",
    "value": 2566667
  },
  {
    "code": "001-102",
    "value": 208500
  },
  {
    "code": "001-439",
    "value": 22000
  },
  {
    "code": "001-455",
    "value": 154
  },
  {
    "code": "001-435",
    "value": 133333
  },
  {
    "code": "001-717",
    "value": 133
  },
  {
    "code": "001-436",
    "value": 95
  },
  {
    "code": "001-697",
    "value": 130
  },
  {
    "code": "001-660",
    "value": 0
  },
  {
    "code": "001-188",
    "value": 79320
  },
  {
    "code": "001-475",
    "value": 79320
  },
  {
    "code": "001-201",
    "value": 31200
  },
  {
    "code": "001-389",
    "value": 1133
  },
  {
    "code": "001-362",
    "value": 180
  },
  {
    "code": "002-7",
    "value": 70
  },
  {
    "code": "002-9",
    "value": 15000
  },
  {
    "code": "001-69",
    "value": 101
  },
  {
    "code": "001-218",
    "value": 0
  },
  {
    "code": "001-249",
    "value": 7200
  },
  {
    "code": "001-161",
    "value": 5415
  },
  {
    "code": "001-112",
    "value": 210000
  },
  {
    "code": "001-345",
    "value": 198800
  },
  {
    "code": "001-379",
    "value": 103000
  },
  {
    "code": "001-59",
    "value": 77433.71
  },
  {
    "code": "001-62",
    "value": 800000
  },
  {
    "code": "001-573",
    "value": 2594070
  },
  {
    "code": "001-39",
    "value": 2749090
  },
  {
    "code": "001-378",
    "value": 28275
  },
  {
    "code": "001-190",
    "value": 0
  },
  {
    "code": "001-434",
    "value": 590
  },
  {
    "code": "001-136",
    "value": 378
  },
  {
    "code": "001-701",
    "value": 763
  },
  {
    "code": "001-100",
    "value": 5586.64
  },
  {
    "code": "001-119",
    "value": 3020
  },
  {
    "code": "001-591",
    "value": 3713
  },
  {
    "code": "001-462",
    "value": 5737
  },
  {
    "code": "001-461",
    "value": 3950
  },
  {
    "code": "001-11.1",
    "value": 65600
  },
  {
    "code": "001-723",
    "value": 44
  },
  {
    "code": "001-721",
    "value": 44
  },
  {
    "code": "001-719",
    "value": 44
  },
  {
    "code": "001-706",
    "value": 47
  },
  {
    "code": "001-722",
    "value": 109
  },
  {
    "code": "001-720",
    "value": 109
  },
  {
    "code": "001-718",
    "value": 109
  },
  {
    "code": "001-705",
    "value": 4955
  },
  {
    "code": "001-16",
    "value": 1000
  },
  {
    "code": "001-186",
    "value": 0
  },
  {
    "code": "001-74",
    "value": 10500
  },
  {
    "code": "001-269",
    "value": 0
  },
  {
    "code": "001-19",
    "value": 75000
  },
  {
    "code": "001-391",
    "value": 166700
  },
  {
    "code": "001-424",
    "value": 17307.54
  },
  {
    "code": "001-12.1",
    "value": 64500
  },
  {
    "code": "001-658",
    "value": 151823
  },
  {
    "code": "001-197",
    "value": 0
  },
  {
    "code": "001-79",
    "value": 485900
  },
  {
    "code": "001-270",
    "value": 0
  },
  {
    "code": "001-158",
    "value": 0
  },
  {
    "code": "001-219",
    "value": 1025000
  },
  {
    "code": "001-29",
    "value": 1500000
  },
  {
    "code": "001-120",
    "value": 1700000
  },
  {
    "code": "001-488",
    "value": 18300
  },
  {
    "code": "001-193",
    "value": 25000
  },
  {
    "code": "001-128",
    "value": 103500
  },
  {
    "code": "001-246",
    "value": 125000
  },
  {
    "code": "001-13.1",
    "value": 85211
  },
  {
    "code": "001-114",
    "value": 72900
  },
  {
    "code": "001-642",
    "value": 137098
  },
  {
    "code": "001-445",
    "value": 245000
  },
  {
    "code": "001-692",
    "value": 72900
  },
  {
    "code": "002-19",
    "value": 12668
  },
  {
    "code": "001-117",
    "value": 20000
  },
  {
    "code": "001-207",
    "value": 2500
  },
  {
    "code": "001-208",
    "value": 0
  },
  {
    "code": "001-64",
    "value": 8150
  },
  {
    "code": "001-328",
    "value": 198200
  },
  {
    "code": "001-206",
    "value": 0
  },
  {
    "code": "001-205",
    "value": 0
  },
  {
    "code": "001-195",
    "value": 0
  },
  {
    "code": "001-71",
    "value": 4702
  },
  {
    "code": "001-340",
    "value": 18091
  },
  {
    "code": "TP-7.21m",
    "value": 5000
  },
  {
    "code": "TP-7.1",
    "value": 3600
  },
  {
    "code": "001-394",
    "value": 315000
  },
  {
    "code": "001-343",
    "value": 450000
  },
  {
    "code": "001-92",
    "value": 0
  },
  {
    "code": "001-482",
    "value": 4816
  },
  {
    "code": "001-91",
    "value": 114.75
  },
  {
    "code": "001-241",
    "value": 110000
  },
  {
    "code": "001-677",
    "value": 43100
  },
  {
    "code": "001-360",
    "value": 198167
  },
  {
    "code": "001-696",
    "value": 641
  },
  {
    "code": "001-689",
    "value": 641
  },
  {
    "code": "001-46",
    "value": 76200000
  },
  {
    "code": "001-115",
    "value": 4097.92
  },
  {
    "code": "001-154",
    "value": 330000
  },
  {
    "code": "001-78",
    "value": 1375091.9
  },
  {
    "code": "001-87",
    "value": 85000
  },
  {
    "code": "001-63",
    "value": 9000
  },
  {
    "code": "001-212",
    "value": 2420
  },
  {
    "code": "001-213",
    "value": 1356
  },
  {
    "code": "001-485",
    "value": 175800
  },
  {
    "code": "001-588",
    "value": 346000
  },
  {
    "code": "001-593",
    "value": 142800
  },
  {
    "code": "001-594",
    "value": 37700
  },
  {
    "code": "001-619",
    "value": 44474
  }
]';

        $data = json_decode($json, true);

        foreach ($data as $row) {
            $item = Item::where('code', $row['code'])->first();
            $value = str_replace(',', '', $row['value']); // COGS
            echo $row['code'] . ' => ' . $row['value'] . PHP_EOL;
            
            if ($item) {
                $inventories = Inventory::orderBy('form_date', 'desc')
                    ->get()
                    ->unique(function ($inventory) {
                        return $inventory['item_id'].$inventory['warehouse_id'];
                    });

                foreach ($inventories as $inventory) {
                    // TODO: Delete all item from warehouse to, so cogs, total quantity, total value is reset to 0
                    $form_date = '2026-08-01 00:00:00';
                    $form_number = FormulirHelper::number('point-inventory-stock-correction', $form_date);

                    $formulir = new Formulir;
                    $formulir->form_date = $form_date;
                    $formulir->form_number = $form_number['form_number'];
                    $formulir->form_raw_number = $form_number['raw'];
                    $formulir->notes = 'Cutoff Stock 2026-08-01';
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
                    $stock_correction->warehouse_id = $inventory->warehouse_id;
                    $stock_correction->save();
                    
                    $stock_correction_item = new StockCorrectionItem;
                    $stock_correction_item->point_inventory_stock_correction_id = $stock_correction->id;
                    $stock_correction_item->item_id = $item->id;
                    $stock_correction_item->stock_in_database = $inventory->total_quantity;
                    $stock_correction_item->quantity_correction = $inventory->total_quantity * -1;
                    $stock_correction_item->correction_notes = 'Cutoff Stock 2026-08-01';
                    $unit = $stock_correction_item->item->unit()->first();
                    $stock_correction_item->unit = $unit->name;
                    $stock_correction_item->converter = $unit->converter;
                    $stock_correction_item->save();

                    $this->comment($inventory);

                    $inventory = new Inventory;
                    $inventory->form_date = date('Y-m-d H:i:s');
                    $inventory->formulir_id = $stock_correction->formulir_id;
                    $inventory->warehouse_id = $stock_correction->warehouse_id;
                    $inventory->item_id = $stock_correction_item->item_id;
                    $inventory->quantity = $stock_correction_item->quantity_correction;
                    $inventory->price = $inventory->cogs ?? 0;
                    
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

                // ----

                // $inventory = Inventory::where('item_id', '=', $item->id)
                //     ->where('form_date', '<', '2026-08-01')
                //     ->orderBy('form_date', 'desc')
                //     ->orderBy('formulir_id', 'desc')
                //     ->first();

                // if ($inventory) {
                //     $form_date = '2026-08-01 00:00:00';
                //     $form_number = FormulirHelper::number('point-inventory-stock-correction', $form_date);

                //     $formulir = new Formulir;
                //     $formulir->form_date = $form_date;
                //     $formulir->form_number = $form_number['form_number'];
                //     $formulir->form_raw_number = $form_number['raw'];
                //     $formulir->notes = 'Cutoff Stock 2026-08-01';
                //     $formulir->approval_to = 1;
                //     $formulir->approval_status = 1;
                //     $formulir->approval_message = '';
                //     $formulir->created_by = 1;
                //     $formulir->updated_by = 1;
                //     if (!$formulir->save()) {
                //         gritter_error('create has been failed', false);
                //     }

                //     $stock_correction = new StockCorrection;
                //     $stock_correction->formulir_id = $formulir->id;
                //     $stock_correction->warehouse_id = app('request')->input('warehouse_id');
                //     $stock_correction->save();

                //     for ($i=0 ; $i<count(app('request')->input('item_id')) ; $i++) {
                //         $stock_correction_item = new StockCorrectionItem;
                //         $stock_correction_item->point_inventory_stock_correction_id = $stock_correction->id;
                //         $stock_correction_item->item_id = $item->id;
                //         $stock_correction_item->stock_in_database = $inventory->total_quantity;
                //         $stock_correction_item->quantity_correction = $inventory->total_quantity;
                //         $stock_correction_item->correction_notes = 'Cutoff Stock 2026-08-01';
                //         $unit = $stock_correction_item->item->unit()->first();
                //         $stock_correction_item->unit = $unit->name;
                //         $stock_correction_item->converter = $unit->converter;
                //         $stock_correction_item->save();
                //     }

                //     foreach ($stock_correction->items as $stock_correction_item) {
                //         $inventory = new Inventory;
                //         $inventory->form_date = date('Y-m-d H:i:s');
                //         $inventory->formulir_id = $stock_correction->formulir_id;
                //         $inventory->warehouse_id = $stock_correction->warehouse_id;
                //         $inventory->item_id = $stock_correction_item->item_id;
                //         $inventory->quantity = $stock_correction_item->quantity_correction;
                //         $inventory->price = $inventory->cogs;
                        
                //         if ($inventory->quantity < 0) {
                //             $inventory->quantity *= -1;
                //             $inventory_helper = new InventoryHelper($inventory);
                //             $inventory_helper->out();
                //         } else {
                //             $inventory_helper = new InventoryHelper($inventory);
                //             $inventory_helper->in();
                //         }
                //     }
                // }
            }
        }
    }
}