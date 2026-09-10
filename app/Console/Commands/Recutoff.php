<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Allocation;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;

class Recutoff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:recutoff';

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
        $this->comment('recalculating inventory');

        \DB::beginTransaction();

        $cutoff_account_csv = [
 {
   "coa_code": 111.01,
   "coa_name": "Kas LAPANGAN PECATU",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 111.02,
   "coa_name": "KAS SETARA KAS",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 111.03,
   "coa_name": "kas estimasi budget hpp",
   "debit": 491309430.05,
   "credit": null
 },
 {
   "coa_code": 111.99,
   "coa_name": "POS SILANG",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 112.01,
   "coa_name": "BRI - 057201001713300 AC ARUBATO PRATAMA MANDIRI DAN BUMI MUTIARA PRATAMA",
   "debit": 380474,
   "credit": null
 },
 {
   "coa_code": 112.02,
   "coa_name": "DANAMON AC 008800278031 an. PT. BUMI MUTIARA PRATAMA",
   "debit": 270468.58,
   "credit": null
 },
 {
   "coa_code": 112.03,
   "coa_name": "SINARMAS AC 0053587488 an. PT Bumi Mutiara Pratama",
   "debit": 19405524,
   "credit": null
 },
 {
   "coa_code": 112.04,
   "coa_name": "BCA AC 2588202769 an. RICHMON KORISTON",
   "debit": 371066.66,
   "credit": null
 },
 {
   "coa_code": 112.05,
   "coa_name": "BCA 258-4062222 an. PT BUMI MUTIARA PRATAMA",
   "debit": 102778864.01,
   "credit": null
 },
 {
   "coa_code": 112.06,
   "coa_name": "MANDIRI AC 1420060002796 a/n PT. BUMI MUTIARA PRATAMA",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 112.07,
   "coa_name": "UOB AC 303-304-696-9 AN PT BUMI MUTIARA PRATAMA",
   "debit": 12860067,
   "credit": null
 },
 {
   "coa_code": 112.08,
   "coa_name": "bca pak hamid",
   "debit": 655806.88,
   "credit": null
 },
 {
   "coa_code": 112.09,
   "coa_name": "BANK ......",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 112.1,
   "coa_name": "DEPOSITO",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 112.12,
   "coa_name": "BPD BALI AC 0270111000662 an. PT. BUMI MUTIARA PRATAMA",
   "debit": 2369938.28,
   "credit": null
 },
 {
   "coa_code": 113.01,
   "coa_name": "Wesel Tagih",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 114.01,
   "coa_name": "Piutang Dagang",
   "debit": 1973852716.05,
   "credit": null
 },
 {
   "coa_code": 115.01,
   "coa_name": "Piutang Direksi",
   "debit": -310024133.25,
   "credit": null
 },
 {
   "coa_code": 115.02,
   "coa_name": "Piutang Karyawan",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 115.03,
   "coa_name": "Piutang (Utang) - rumah pak putra",
   "debit": -31937080.77,
   "credit": null
 },
 {
   "coa_code": 115.04,
   "coa_name": "Piutang (Utang) - Ce Fei-Fei",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 115.05,
   "coa_name": "Piutang (Utang) - …",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 115.99,
   "coa_name": "Piutang Lain-lain",
   "debit": 258218634,
   "credit": null
 },
 {
   "coa_code": 116.01,
   "coa_name": "SEDIAAN",
   "debit": 1389316635.62,
   "credit": null
 },
 {
   "coa_code": 116.02,
   "coa_name": "SediaaN IN TRANSIT",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 116.03,
   "coa_name": "Sediaan….",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 116.99,
   "coa_name": "Sediaan Lain-lain",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 117.01,
   "coa_name": "PPh 23 Dibayar Dimuka",
   "debit": 2418460,
   "credit": null
 },
 {
   "coa_code": 117.02,
   "coa_name": "PPh 25 Dibayar Dimuka",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 117.03,
   "coa_name": "PPh final ps 4 ayat 2",
   "debit": 1166437320.85,
   "credit": null
 },
 {
   "coa_code": 117.04,
   "coa_name": "Asuransi Dibayar Dimuka",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 117.05,
   "coa_name": "Sewa Dibayar Dimuka",
   "debit": 5000000,
   "credit": null
 },
 {
   "coa_code": 117.06,
   "coa_name": "Uang Muka Pembelian",
   "debit": 1937500,
   "credit": null
 },
 {
   "coa_code": 117.07,
   "coa_name": "PPN Masukan",
   "debit": 912476975.75,
   "credit": null
 },
 {
   "coa_code": 117.08,
   "coa_name": "Biaya Dibayar Dimuka",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 117.09,
   "coa_name": "Promosi Dibayar Dimuka",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 117.99,
   "coa_name": "Lain-lain Dibayar Dimuka",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 12,
   "coa_name": "Aktiva Lancar",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 121.01,
   "coa_name": "TANAH",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 122.01,
   "coa_name": "Bangunan",
   "debit": 314562135,
   "credit": null
 },
 {
   "coa_code": 122.02,
   "coa_name": "Akumulasi Penyusutan Bangunan",
   "debit": -34077564.63,
   "credit": null
 },
 {
   "coa_code": 123.01,
   "coa_name": "Mesin dan Peralatan Pabrik",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 123.02,
   "coa_name": "Akumulasi Penyusutan Mesin dan Peralatan Pabrik",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 124.01,
   "coa_name": "Instalasi",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 124.02,
   "coa_name": "Akumulasi Penyusutan Instalasi",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 125.01,
   "coa_name": "Inventaris Pabrik",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 125.02,
   "coa_name": "Akumulasi Penyusutan Inventaris Pabrik",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 126.01,
   "coa_name": "Inventaris Kantor",
   "debit": 57337059.6,
   "credit": null
 },
 {
   "coa_code": 126.02,
   "coa_name": "Akumulasi Penyusutan Inventaris Kantor",
   "debit": -30413650.18,
   "credit": null
 },
 {
   "coa_code": 127.01,
   "coa_name": "Inventaris Penjualan",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 127.02,
   "coa_name": "Akumulasi Penyusutan Inventaris Penjualan",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 128.01,
   "coa_name": "Kendaraan dan Alat Angkut Pabrik",
   "debit": 7500000,
   "credit": null
 },
 {
   "coa_code": 128.02,
   "coa_name": "Akumulasi Penyusutan Kendaraan Pabrik",
   "debit": -1406250,
   "credit": null
 },
 {
   "coa_code": 129.01,
   "coa_name": "Kendaraan Kantor",
   "debit": 613542083,
   "credit": null
 },
 {
   "coa_code": 129.02,
   "coa_name": "Akumulasi Penyusutan Kendaraan Kantor",
   "debit": -72168693.55,
   "credit": null
 },
 {
   "coa_code": 130.01,
   "coa_name": "Kendaraan Penjualan",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 130.02,
   "coa_name": "Akumulasi Penyusutan Kendaraan Penjualan",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 131.01,
   "coa_name": "Aktiva Tak Berwujud",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 131.02,
   "coa_name": "Akumulasi Amortisasi Aktiva Tak Berwujud",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 132.01,
   "coa_name": "Aktiva Dalam Proses",
   "debit": 0,
   "credit": null
 },
 {
   "coa_code": 133.01,
   "coa_name": "Investasi KOPI",
   "debit": 1867500000,
   "credit": null
 },
 {
   "coa_code": 211.01,
   "coa_name": "Wesel Bayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 212.01,
   "coa_name": "Utang Dagang",
   "debit": null,
   "credit": -60000000
 },
 {
   "coa_code": 213.01,
   "coa_name": "Utang Ekspedisi",
   "debit": null,
   "credit": -1011300
 },
 {
   "coa_code": 213.02,
   "coa_name": "AYAT SILANG - PROYEK (ESTIMASI BUDGET)",
   "debit": null,
   "credit": -2735417324.26
 },
 {
   "coa_code": 214.01,
   "coa_name": "Utang Direksi",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 214.02,
   "coa_name": "Utang Pembelian Aktiva",
   "debit": null,
   "credit": -310750000
 },
 {
   "coa_code": 214.04,
   "coa_name": "Utang pihak ke iii",
   "debit": null,
   "credit": -875968145
 },
 {
   "coa_code": 214.05,
   "coa_name": "Utang PPH FINAL 4 AYAT 2",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 214.06,
   "coa_name": "Utang PPH 21",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 214.07,
   "coa_name": "Utang - rumah pak putra",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 214.08,
   "coa_name": "uTANG PAK ALBERT",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 214.99,
   "coa_name": "Utang Lain-lain",
   "debit": null,
   "credit": -1103335849.55
 },
 {
   "coa_code": 215.01,
   "coa_name": "Gaji YMH Dibayar",
   "debit": null,
   "credit": -350000000
 },
 {
   "coa_code": 215.02,
   "coa_name": "Listrik, Air & Telpon YMH Dibayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.03,
   "coa_name": "Asuransi YMH Dibayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.04,
   "coa_name": "THR YMH Dibayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.05,
   "coa_name": "Komisi yang Masih Harus Dibayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.06,
   "coa_name": "PPN Keluaran",
   "debit": null,
   "credit": -1467370740.04
 },
 {
   "coa_code": 215.07,
   "coa_name": "Cadangan Bad Debt",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.08,
   "coa_name": "Cadangan Pemeriksaan Pajak",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.09,
   "coa_name": "Poin Yang Masih Harus Dibayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.1,
   "coa_name": "Sewa Yang Masih Harus Dibayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.11,
   "coa_name": "Cadangan Biaya Truk",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.12,
   "coa_name": "PPh 23 YMH Dibayar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 215.99,
   "coa_name": "Lain-lain YMH Dibayar",
   "debit": null,
   "credit": -75761556
 },
 {
   "coa_code": 216.01,
   "coa_name": "Uang Muka Penjualan",
   "debit": null,
   "credit": -59515485
 },
 {
   "coa_code": 221.01,
   "coa_name": "Hutang kepada pihak ketiga",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 311,
   "coa_name": "Modal Pemilik",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 312,
   "coa_name": "Deviden",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 321,
   "coa_name": "Laba Rugi s/d Tahun Lalu",
   "debit": null,
   "credit": -1297943531.71
 },
 {
   "coa_code": 322,
   "coa_name": "Laba Rugi s/d Bulan Lalu",
   "debit": null,
   "credit": 121009515.38
 },
 {
   "coa_code": 323,
   "coa_name": "Laba Rugi Bulan Berjalan",
   "debit": null,
   "credit": null
 },
 {
   "coa_code": 411.01,
   "coa_name": "Penjualan",
   "debit": null,
   "credit": -522742300
 },
 {
   "coa_code": 411.02,
   "coa_name": "REDUKSI PeKERJAAN",
   "debit": null,
   "credit": 10965021.08
 },
 {
   "coa_code": 411.03,
   "coa_name": "Pendapatan (Beban) Selisih Kas",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 411.04,
   "coa_name": "Pendapatan Angkutan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 411.05,
   "coa_name": "Pendapatan Lain-lain",
   "debit": null,
   "credit": -3893008
 },
 {
   "coa_code": 412,
   "coa_name": "beban pokok penjualan",
   "debit": null,
   "credit": 58542138.6
 },
 {
   "coa_code": 412.01,
   "coa_name": "ESTIMASI BUDGET HPP",
   "debit": null,
   "credit": -240948461.6
 },
 {
   "coa_code": 412.02,
   "coa_name": "Pembelian Bahan Pembantu",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 412.03,
   "coa_name": "Pembelian Bahan Kemasan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 412.04,
   "coa_name": "Pembelian Bahan Campuran",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 412.05,
   "coa_name": "Pembelian Bahan Lain-lain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.01,
   "coa_name": "Beban Tenaga Kerja Langsung",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.02,
   "coa_name": "Pemakaian Bahan Baku",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.03,
   "coa_name": "Pemakaian Bahan Pembantu",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.04,
   "coa_name": "Pemakaian Bahan Kemasan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.05,
   "coa_name": "Pemakaian Bahan Campuran",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.06,
   "coa_name": "Pemakaian Bahan Lain-lain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.07,
   "coa_name": "Beban Tenaga Kerja Tidak Langsung",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.08,
   "coa_name": "BIAYA EKSPEDISI",
   "debit": null,
   "credit": 1025000
 },
 {
   "coa_code": 413.09,
   "coa_name": "BIAYA PROYEK",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.1,
   "coa_name": "Listrik",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.11,
   "coa_name": "Air",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.12,
   "coa_name": "PPh 21 Pabrik",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.13,
   "coa_name": "Pemeliharaan Bangunan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.14,
   "coa_name": "Pemeliharaan Mesin dan Peralatan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.15,
   "coa_name": "Pemeliharaan Inventaris",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.16,
   "coa_name": "Pemeliharaan dan Surat Kendaraan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.17,
   "coa_name": "Pemakaian Bahan Bakar",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.18,
   "coa_name": "Penyusutan Bangunan",
   "debit": null,
   "credit": 1310675.56
 },
 {
   "coa_code": 413.19,
   "coa_name": "Penyusutan Mesin dan Peralatan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.2,
   "coa_name": "Penyusutan Instalasi Listrik",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.21,
   "coa_name": "Penyusutan Inventaris",
   "debit": null,
   "credit": 1025122.5
 },
 {
   "coa_code": 413.22,
   "coa_name": "Penyusutan Kendaraan",
   "debit": null,
   "credit": 78125
 },
 {
   "coa_code": 413.23,
   "coa_name": "Sewa Bangunan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.24,
   "coa_name": "Biaya Perlengkapan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.25,
   "coa_name": "Asuransi",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.26,
   "coa_name": "Biaya Amortisasi Aktiva Tak Berwujud",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.27,
   "coa_name": "Biaya Selisih Sediaan",
   "debit": null,
   "credit": 851521.96
 },
 {
   "coa_code": 413.28,
   "coa_name": "Biaya SKBDN",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 413.99,
   "coa_name": "Beban FOH lain-lain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.01,
   "coa_name": "Gaji dan Tunjangan Karyawan Kantor",
   "debit": null,
   "credit": 12374610
 },
 {
   "coa_code": 511.02,
   "coa_name": "Konsumsi, Pengobatan, dan lain-lain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.03,
   "coa_name": "Suplai dan Alat Administrasi",
   "debit": null,
   "credit": 40000
 },
 {
   "coa_code": 511.04,
   "coa_name": "LISTRIK KANTOR",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.05,
   "coa_name": "AIR KANTOR",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.06,
   "coa_name": "PPh 21 Kantor",
   "debit": null,
   "credit": 474859
 },
 {
   "coa_code": 511.07,
   "coa_name": "Administrasi Bank",
   "debit": null,
   "credit": 551650
 },
 {
   "coa_code": 511.08,
   "coa_name": "Pos & Dokumen",
   "debit": null,
   "credit": 15000
 },
 {
   "coa_code": 511.09,
   "coa_name": "Honorarium Konsultan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.1,
   "coa_name": "Pemeliharaan Inventaris Kantor",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.11,
   "coa_name": "Pemeliharaan dan Surat Kendaraan Kantor",
   "debit": null,
   "credit": 270000
 },
 {
   "coa_code": 511.12,
   "coa_name": "Penyusutan Inventaris Kantor",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.13,
   "coa_name": "Penyusutan Kendaraan Kantor",
   "debit": null,
   "credit": 6391063.36
 },
 {
   "coa_code": 511.14,
   "coa_name": "ASURANSI OPERASIONAL KANTOR",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.15,
   "coa_name": "THR",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.16,
   "coa_name": "Perijinan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 511.99,
   "coa_name": "Beban Umum & Administrasi Lain-lain",
   "debit": null,
   "credit": 7112552
 },
 {
   "coa_code": 512.01,
   "coa_name": "Gaji dan Tunjangan Karyawan Penjualan",
   "debit": null,
   "credit": 148735548
 },
 {
   "coa_code": 512.02,
   "coa_name": "KONSUMSI, PENGOBATAN, DAN LAIN-LAIN PENJUALAN",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.03,
   "coa_name": "SUPLAI DAN ALAT ADMINISTRASI PENJUALAN",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.04,
   "coa_name": "Iklan dan Promosi",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.05,
   "coa_name": "Telpon, HP, & Faximile",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.06,
   "coa_name": "Transport (Parkir, Tol dan Bensin)",
   "debit": null,
   "credit": 3743630
 },
 {
   "coa_code": 512.07,
   "coa_name": "komisi penjualan",
   "debit": null,
   "credit": 4752202
 },
 {
   "coa_code": 512.08,
   "coa_name": "PPh 21 Penjualan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.09,
   "coa_name": "Pemeliharaan Inventaris Penjualan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.1,
   "coa_name": "Pemeliharaan & Surat Kendaraan Penjualan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.11,
   "coa_name": "Penyusutan Inventaris Penjualan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.12,
   "coa_name": "Penyusutan Kendaraan Penjualan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.13,
   "coa_name": "Amortisasi Aktiva Tak Berwujud",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.14,
   "coa_name": "Representasi dan Sumbangan",
   "debit": null,
   "credit": 2000000
 },
 {
   "coa_code": 512.15,
   "coa_name": "Pajak Bumi dan Bangunan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.16,
   "coa_name": "Entertain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.17,
   "coa_name": "Perjalanan Dinas",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.18,
   "coa_name": "Ongkos Angkut Penjualan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.19,
   "coa_name": "Kerugian Piutang Tak Tertagih",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.2,
   "coa_name": "Beban Pajak Badan",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 512.99,
   "coa_name": "Beban Penjualan Lain-lain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 611.01,
   "coa_name": "Pendapatan Bunga Bank",
   "debit": null,
   "credit": -5707.62
 },
 {
   "coa_code": 611.02,
   "coa_name": "Pendapatan Bunga Pinjaman Pihak Ke-3",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 611.03,
   "coa_name": "Pendapatan (Beban) Selisih Pembayaran",
   "debit": null,
   "credit": 0.39
 },
 {
   "coa_code": 611.04,
   "coa_name": "Laba (Rugi) Penjualan Aktiva Tetap",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 611.99,
   "coa_name": "Pendapatan Non Operasional Lain-lain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 711.01,
   "coa_name": "Beban Bunga Leasing",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 711.02,
   "coa_name": "Beban Bunga Pinjaman Pihak Ke-3",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 711.03,
   "coa_name": "Beban (Pendapatan) Selisih Kurs",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 711.99,
   "coa_name": "Beban Non Operasional Lain-lain",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 811.01,
   "coa_name": "Beban Pajak",
   "debit": null,
   "credit": 2921387
 },
 {
   "coa_code": 811.02,
   "coa_name": "Beban Management Fee",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 811.03,
   "coa_name": "Alokasi Dana Annuation",
   "debit": null,
   "credit": 0
 },
 {
   "coa_code": 999,
   "coa_name": "Ikthisar Laba Rugi",
   "debit": null,
   "credit": 0
 }
];

        $form_date = '2026-08-01 00:00:00';

        $form_number = FormulirHelper::number('point-accounting-cut-off-account', $form_date);

        $formulir = new Formulir;
        $formulir->form_date = $form_date;
        $formulir->form_number = $form_number['form_number'];
        $formulir->form_raw_number = $form_number['raw'];
        $formulir->notes = '';
        $formulir->approval_to = 1;
        $formulir->approval_status = 0;
        $formulir->approval_message = '';
        $formulir->created_by = 1;
        $formulir->updated_by = 1;
        $formulir->save();

        $cut_off_account = new CutOffAccount;
        $cut_off_account->formulir_id = $formulir->id;
        $cut_off_account->save();

        $coa = Coa::where('coa_number', $account['coa_code'])->first();
        
        foreach ($cutoff_account_csv as $account) {
            $cut_off_account_detail = new CutOffAccountDetail;
            $cut_off_account_detail->cut_off_account_id = $cut_off_account->id;
            $cut_off_account_detail->coa_id = $coa->id;
            $cut_off_account_detail->debit = $account->debit;
            $cut_off_account_detail->credit = $account->credit;
            $cut_off_account_detail->save();
        }

        \DB::commit();
    }
}
