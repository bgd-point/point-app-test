<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffPayableDetail;
use Point\PointAccounting\Models\CutOffReceivable;
use Point\PointAccounting\Models\CutOffReceivableDetail;
use Point\PointInventory\Helpers\StockCorrectionHelper;
use Point\PointInventory\Models\StockCorrection\StockCorrection;

class InjectCutOffBumiMandiriSeeder extends Seeder
{
    public function run()
    {
    	\DB::beginTransaction();
        \Log::info('---- seeder cutoff started ----');
        self::uangMukaPembelian();
        self::uangMukaPenjualan();
        \Log::info('---- seeder cutoff finished ----');
        \DB::commit();
    }

    public function uangMukaPembelian()
    {
    	$cutoff_payable = CutOffPayable::where('formulir_id', 3)->first();

        $persons = array('Sinar Pembangunan Abadi (SPA)', 'Sinar Pembangunan Abadi (SPA)', 'Gramitrama Jaya Steel');
    	$data = array(1160000000, 500000000, 122200000);
    	for ($i=0; $i < count($data); $i++) { 
            $person = self::personCreate(1, 1, $persons[$i]);

    		$cutoff_payable_detail = new CutOffPayableDetail;
	    	$cutoff_payable_detail->cut_off_payable_id = $cutoff_payable->id;
	    	$cutoff_payable_detail->coa_id = 11; // COA name 'uang muka pembelian'
	    	$cutoff_payable_detail->subledger_id = $person->id;
	    	$cutoff_payable_detail->subledger_type = get_class(new Person);
	    	$cutoff_payable_detail->amount = $data[$i];
	    	$cutoff_payable_detail->save();	
    	}
    }

    public function uangMukaPenjualan()
    {
        $cutoff_receivable = CutOffReceivable::where('formulir_id', 477)->first();

        $persons = array('Berkat Jaya Bangunan', 'Surya Baru', 'Surya Baru', 'Surya Baru (Luwuk)',
            'Surya Baru (Luwuk)', 'Fandi', 'Bumi Indo Moker');
        $data = array(46137500, 3850000, 564333000, 80224000, 174605700, 150000000, 35190000);
        for ($i=0; $i < count($data); $i++) { 
    	    $person = self::personCreate(2, 5, $persons[$i]);
    		$cutoff_receivable_detail = new CutOffReceivableDetail;
	    	$cutoff_receivable_detail->cut_off_receivable_id = $cutoff_receivable->id;
	    	$cutoff_receivable_detail->coa_id = 34; // COA name 'uang muka penjualan'
	    	$cutoff_receivable_detail->subledger_id = $person->id;
	    	$cutoff_receivable_detail->subledger_type = get_class(new Person);
	    	$cutoff_receivable_detail->amount = $data[$i];
	    	$cutoff_receivable_detail->save();	
    	}
    }

    public static function personCreate($type, $group, $person)
    {
        $person = Person::where('name', $person)->first();
        if ($person) {
            return $person;
        }

    	$person = new Person;
    	$person->person_type_id = $type;
    	$person->person_group_id = $group;
    	$person->created_by = 2;
    	$person->updated_by = 2;
    	$person->save();
    	$person->code = 'CUS-'.$person->id;
    	$person->name = $person;
    	$person->save();

    	return $person;
    }
}
