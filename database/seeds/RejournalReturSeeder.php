<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Journal;

class RejournalReturSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listRetur = \Point\PointSales\Models\Sales\Retur::joinFormulir()
            ->notArchived()
            ->approvalApproved()
            ->select('formulir.id')
            ->get()
            ->toArray();

        Journal::whereIn('form_journal_id', $listRetur)->delete();

        $listRetur = \Point\PointSales\Models\Sales\Retur::joinFormulir()
            ->where('formulir.form_status', '>=', 0)
            ->notArchived()
            ->approvalApproved()
            ->select('point_sales_retur.*')
            ->get();

        foreach ($listRetur as $retur) {
            // PENJUALAN (DEBIT)
            $sales_of_goods = JournalHelper::getAccount('point sales indirect', 'sale of goods');
            $journal = new Journal;
            $journal->form_date = $retur->formulir->form_date;
            $journal->coa_id = $sales_of_goods;
            $journal->description = 'retur invoice ' . $retur->invoice->formulir->form_number;
            $journal->debit = $retur->total;
            $journal->form_journal_id = $retur->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();

            // ACCOUNT RECEIVEABLE (CREDIT)
            $account_receivable = JournalHelper::getAccount('point sales indirect', 'account receivable');
            $journal = new Journal;
            $journal->form_date = $retur->formulir->form_date;
            $journal->coa_id = $account_receivable;
            $journal->description = 'retur invoice ' . $retur->formulir->form_number;
            $journal->credit = $retur->total;
            $journal->form_journal_id = $retur->formulir->id;
            $journal->form_reference_id;
            $journal->subledger_id = $retur->person_id;
            $journal->subledger_type = get_class($retur->person);
            $journal->save();

            // INVENTORY (DEBIT)
            foreach ($retur->items as $returItem) {

                $itemArray = [
                    "[R001] KOPI BARA 70 GR",
                    "[002-1] kopi dapoer iboe 60 gr",
                    "[002-2] teh vanilla black 50 gr",
                    "[002-3] teh jasmin 25 gr",
                    "[002-4] tarik hijau 370 ml",
                    "[002-5] tarik merah 495 ml",
                    "[002-6] gelas es sablon",
                    "[002-7] sedotan es",
                    "[002-8] gelas panas sablon",
                    "[002-9] sedotan panas",
                    "[002-10] GULA PUTIH",
                    "[002-11] KRESEK TENTENG GELAS",
                    "[001-2] GELAS KACA PROMOSI",
                    "[001-3] PLASTIK PACKAGING ROLL",
                    "[001-4] KARTON",
                    "[001-5] PAKET KARTON KOPI RETAIL",
                    "[001-7] PAKET ECERAN KOPI RETAIL",
                    "[001-8] PAKET RENCENG KOPI RETAIL",
                    "[001-9] plastik packaging pcs",
                    "[001-10] KOTAK PUTIH SAMPLE KOPI",
                    "[001-12] KOPI 70GR",
                    "[001-13] GELAS SAMPLING",
                    "[001-14] TABLE TOP DISPLAY",
                    "[001-15] KOPI BARA BIJI",
                    "[001-16] STIKER TOKO 20X20 ",
                    "[001-17] KIPAS ANGIN",
                    "[001-18] DISPENSER",
                    "[001-19] TAS DELIVERY KOPI SACHET",
                    "[001-20] GULA SERVING",
                    "[001-21] GIFT BOX BESAR",
                    "[R003-P] KOPI BARA CUP 2 IN 1 GULA PISAH",
                    "[001-23] GIFT PACK KECIL",
                    "[001-24] KOPI BARA CUP",
                    "[001-25] PACKAGING KOPI SACHET 10GR",
                    "[001-26] BOTOL 250 ML",
                    "[001-27] STANDING POUCH MERAH",
                    "[001-28] STANDING POUCH HITAM",
                    "[001-29] TENDA LIPAT 2X3",
                    "[001-30] BOX HITAM KOPI BARA KECIL",
                    "[001-31] KAOS SERAGAM KOPI BARA",
                    "[001-32] KEMEJA SERAGAM KOPI BARA",
                    "[001-33] KOPI DOKAR 30GR",
                    "[001-34] KOPI DOKAR 250GR",
                    "[001-35] PLASTIK PACKAGING ROLL CPP35",
                    "[001-37] GELAS PANAS SABLON PAITUA",
                    "[001-40] mikrotik rb951ui",
                    "[001-41] modem TP-LINK 300Mbps TL-WA801ND",
                    "[001-42] Modem WiFi Huawei E5673",
                    "[001-43] Plastic	Container",
                    "[001-44] power cable (4 outlet)",
                    "[001-45] setup and instalation",
                    "[001-47] box cup polos",
                    "[001-48] papercup 8oz custom print 2 warna lid hitam + stirrer (GELAS PANAS SABLON BROMO)",
                    "[001-49] BOX SINGLE WALL (3 MM)",
                    "[001-50] JAM DINDING 73",
                    "[001-51] GELAS KACA PROMOSI KECIL",
                    "[R002] KOPI BARA 5 GR",
                    "[001-54] PAKET KARTON KOPI BARA 5 GR",
                    "[001-55] KAOS POLO KOPI BARA",
                    "[001-56] KAOS OBLONG KOPI BARA",
                    "[001-57] HELM",
                    "[001-58] MUG PUTIH KOPI BARA",
                    "[001-59] SIDEBAG ",
                    "[001-60] JAKET KOPI BARA",
                    "[001-62] SIGNBOARD",
                    "[001-63] BUKU NOTA PENJUALAN",
                    "[001-64] TOPLES KACA",
                    "[001-65] company profile",
                    "[001-66] PAKET ECERAN KOPI BARA 5 GR",
                    "[001-67] PAKET ECERAN KOPI BARA CUP",
                    "[001-68] clip on usb",
                    "[001-69] SEDOTAN PANAS PCS",
                    "[001-70] goodie bag a4",
                    "[001-71] TOPLES PLASTIK",
                    "[001-73] POUCH MERAH PROMOSI",
                    "[001-74] TALI KARTU NAMA",
                    "[001-75] MAP KOPI BARA",
                    "[R012] Kopi Bara Premium Pack 1 kg",
                    "[B008] Kopi Bara Regular pack 1 kg",
                    "[B011] KOPI BARA REGULER 3 IN 1 BULK 1 KG",
                    "[001-77] BOOTH PROMOTION DESK",
                    "[B001] KOPI BARA REGULER BUBUK 250 GR",
                    "[001-78] WATER BOILER",
                    "[R008] KOPI BARA PREMIUM BIJI SEAL PACK 1KG",
                    "[B005] KOPI BARA REGULER KOPI GULA 20GR YAHUD",
                    "[001-79] TEKO LISTRIK",
                    "[001-81] paket eceran gift pack sedang",
                    "[001-82] PENYARING MESIN KOPI (MICRO FILTER)",
                    "[001-83] MESIN GRINDER HITAM / GILING KOPI (ELECTRIC N600)",
                    "[001-84] TEKO TERMOMETER KOPI (POUR OVER KETTLE THERMOMETER)",
                    "[001-85] PACKAGING POUCH HITAM KOPI 1 KG",
                    "[001-86] DONGKRAK VW COMBI",
                    "[R-003] KOPI BARA PREMIUM CUP 2IN1 ",
                    "[B002] KOPI BARA reguler cup panas kopi gula YAHUD",
                    "[B002 - 1] CUP KOPI BARA reguler cup panas kopi gula B002",
                    "[001-88] GELAS PANAS POLOS",
                    "[001-89] TOPLES PLASTIK WARNA WARNI",
                    "[001-90] TAS KERTAS COKLAT",
                    "[001-91] TUTUP HITAM GELAS PANAS ",
                    "[001-92] TUTUP CEMBUNG GELAS ES",
                    "[001-93] STANDING POUCH HITAM 750 GR",
                    "[B001 - 1] KOPI BARA REGULER BUBUK 20 GR SAMPLE",
                    "[B008 -1 ] KOPI BARA REGULAR HOREKA 25 GR SAMPLE",
                    "[B002 - 99] kopibara CUP Yahuddd samPLE",
                    "[R002 - 99] KOPI BARA BUBUK 5 GR SAMPLE",
                    "[R001 - 99] KOPI BARA BUBUK 70 GR SAMPLE",
                    "[R003 - 99] KOPI BARA 2IN1 CUP 25 GR SAMPLE",
                    "[R012 - 99] KOPI BARA BUBUK PREMIUM 1 KG SAMPLE",
                    "[R008 - 99] KOPI BARA BIJI PREMIUM 1 KG SAMPLE",
                    "[B001 - 99] KOPI BARA BUBUK REGULER 250 GR SAMPLE",
                    "[B008 - 99] KOPI BARA BUBUK HOREKA 1 KG SAMPLE",
                    "[B011 - 99] KOPI BARA BUBUK 3IN1 REGULER 1 KG SAMPLE",
                    "[001-100] STANDING POUCH VALVE HITAM 1 KG",
                    "[001-101] paket eceran gift pack besar",
                    "[001-102] ROLL BANNER",
                    "[Y012] Kopi Bara 3 In 1 Instan 1 Kg ",
                    "[001-103] ASBAK PROMOSI",
                    "[001-104] PAKET COFFEE CORNER",
                    "[001-106] PAKET ECERAN GIFT PACK KECIL",
                    "[001-107] GRINDER COFFE MILL HOPPER PUTIH",
                    "[001-108] AEROPRESS USA",
                    "[001-109] FRENCH PRESS 350 ML",
                    "[001-110] BLOWER",
                    "[001-111] HARIO V60 DRIPPER 01 RED/WHITE",
                    "[001-112] SERVER HARIO RANGE 360 ML",
                    "[001-113] KETEL LEHER ANGSA 1200 WITH THERMO",
                    "[001-114] TIMBANGAN DIGITAL 1 KG",
                    "[001-115] VIETNAM DRIP",
                    "[001-116] NAMI KITCHEN CART RED",
                    "[B005 - 99] KOPI BARA REGULER KOPI GULA 20GR YAHUD SAMPLE",
                    "[R003] KOPI BARA 2IN1 CUP 25 GR",
                    "[001-117] TOPI KOBOI",
                    "[001-119] STANDING POUCH VALVE HITAM 750 GR",
                    "[001-120] TENDA LIPAT 3 X 3",
                    "[001-121] HOM 3 TIERS KITCHEN RACKBEECH",
                    "[001-122] HARIO V60 KIT",
                    "[001-123] LaMPU NEON LED",
                    "[001-124] ELECTRIC STOVE",
                    "[001-125] MOKAPOT 3 CUP",
                    "[001-126] AURALEX 22 CL",
                    "[001-127] KARTON CUP SABLON 2 SISI",
                    "[001-128] TERMOS STAINLESS 1 LTR",
                    "[P009-99] KOPI BARA SPECIALTY BEAN JAR ARABIKA BAJAWA SAMPLE",
                    "[001-132] CUP POLOS 6", "5 OZ",
                    "[001-133] TUTUP CUP 6", "5 OZ",
                    "[001-134] standing pouch hitam small glossy 100gr",
                    "[001-136] standing pouch transparant",
                    "[001-140] HONAI KETEL",
                    "[001-141] SCALE FOIL",
                    "[001-142] LIBBEY - LGC QUARTET DOUBLE OLD.F",
                    "[001-143] CY - PLUNGER 350 ML (TO-0229)",
                    "[001-146] GIFT BOX HITAM TEMATIC",
                    "[001-147] RAK DISPLAY",
                    "[001-149] MIKA GIFT PACK",
                    "[001-150] STANDING POUCH HITAM 1 KG",
                    "[001-152] PAKET ECERAN B005 KOPI BARA REGULER KOPI GULA 20GR YAHUD",
                    "[TP-7.21m] TOPLES TOMAT SEDANG",
                    "[TP-7.1] TOPLES TUTUP MERAH KECIL",
                    "[001-8.1] KOPI BARA 3 IN 1 YAHUD SAMPLE A 999",
                    "[001-9.1] KOPI BARA 3 IN 1 YAHUD SAMPLE B 999",
                    "[001-10.1] KOPI BARA 3 IN 1 YAHUD SAMPLE C 999",
                    "[001-13.1] TIMBANGAN BADAN",
                    "[P012] kopi bara Arabica flores bajawa 1 kg",
                    "[P013] kopi bara Arabica kintamani 1 kg",
                    "[P014] kopi bara Arabica toraja 1 kg",
                    "[P015] kopi bara Arabica arjuno 1 kg"
                ];

                $priceArray = [
                    4340,
                    3400,
                    2400,
                    1500,
                    8542,
                    10729,
                    725,
                    85,
                    685,
                    70,
                    10900,
                    28000,
                    1688,
                    58148,
                    4775,
                    275306,
                    4340,
                    45088,
                    0,
                    2100,
                    6000,
                    100,
                    120000,
                    20500,
                    750,
                    310000,
                    75000,
                    59788,
                    220,
                    85000,
                    620,
                    5000,
                    1746,
                    175,
                    917,
                    1175,
                    1175,
                    1500000,
                    27500,
                    31500,
                    70000,
                    1650,
                    13750,
                    1160000,
                    780,
                    875000,
                    350000,
                    590000,
                    80000,
                    60000,
                    1000000,
                    5273,
                    495,
                    4500,
                    28000,
                    2667,
                    300,
                    230975,
                    42500,
                    23500,
                    300000,
                    20000,
                    95050,
                    350000,
                    800000,
                    7000,
                    8150,
                    14000,
                    377,
                    1305,
                    65000,
                    1,
                    4500,
                    4000,
                    7000,
                    10500,
                    9000,
                    47000,
                    35000,
                    18500,
                    850000,
                    8250,
                    799900,
                    55000,
                    415,
                    150000,
                    38742,
                    74000,
                    800000,
                    400000,
                    4575,
                    255000,
                    620,
                    495,
                    1450,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    415,
                    300,
                    4340,
                    620,
                    47000,
                    55000,
                    8250,
                    35000,
                    18500,
                    5380,
                    93680,
                    208500,
                    27500,
                    3000,
                    2882576,
                    6028,
                    940000,
                    615000,
                    110000,
                    25000,
                    85000,
                    210000,
                    400000,
                    130000,
                    70000,
                    899000,
                    415,
                    495,
                    20000,
                    3020,
                    1700000,
                    639200,
                    300000,
                    198376,
                    156000,
                    160000,
                    20000,
                    7100,
                    103500,
                    27500,
                    500,
                    500,
                    0,
                    0,
                    370000,
                    200000,
                    112000,
                    165000,
                    27500,
                    150000,
                    0,
                    0,
                    5031,
                    5000,
                    4200,
                    525,
                    525,
                    525,
                    85211,
                    145000,
                    155000,
                    138000,
                    93000
                ];

                $price = 0;

                for ($i = 0; $i < count($itemArray); $i++) {
                    if ('['.$returItem->item->code . '] ' . $returItem->item->name == $itemArray[$i]) {
                        $price = $priceArray[$i];
                        break;
                    }
                }

                echo $returItem->item->code . ' ' . $returItem->item->name;
                echo ' = ' . $price;
                echo PHP_EOL;

                $journal = new Journal;
                $journal->form_date = $retur->formulir->form_date;
                $journal->coa_id = $returItem->item->account_asset_id;
                $journal->description = 'retur item "' . $returItem->item->codeName.'"';
                $journal->credit = $price * $returItem->quantity;
                $journal->form_journal_id = $retur->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $returItem->item_id;
                $journal->subledger_type = get_class($returItem->item);
                $journal->save();

                // HPP (CREDIT)
                $cost_of_sales_account = JournalHelper::getAccount('point sales indirect', 'cost of sales');
                $journal = new Journal;
                $journal->form_date = $retur->formulir->form_date;
                $journal->coa_id = $cost_of_sales_account;
                $journal->description = 'retur item "' . $retur->formulir->form_number.'"';
                $journal->debit = $price * $returItem->quantity;
                $journal->form_journal_id = $retur->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }
    }
}
