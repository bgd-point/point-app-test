<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffPayableDetail;

class InjectCutOffBnsSeeder extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();
        \Log::info('---- inject cutoff seeder started ----');
        self::utangDireksi();
        \Log::info('---- inject cutoff seeder finished ----');
        \DB::commit();
    }

    public static function formulirCreate($cutoff_account, $formulir_number_code)
    {
        $form_number = FormulirHelper::number($formulir_number_code, $cutoff_account->formulir->form_date);

        $formulir = new Formulir;
        $formulir->form_date = $cutoff_account->formulir->form_date;
        $formulir->form_number = $form_number['form_number'];
        $formulir->form_raw_number = $form_number['raw'];
        $formulir->notes = 'cutoff payable 31 DEC 2016 ';
        $formulir->approval_to = 1;
        $formulir->approval_status = 1;
        $formulir->form_status = 1;
        $formulir->approval_message = '';
        $formulir->created_by = $cutoff_account->formulir->created_by;
        $formulir->updated_by = $cutoff_account->formulir->created_by;
        $formulir->save();

        return $formulir;
    }

    public function utangDireksi()
    {
        $cutoff_account = CutOffAccount::where('formulir_id', 266)->first();
        $formulir = self::formulirCreate($cutoff_account, 'point-accounting-cut-off-payable');
        $person = self::personCreate(2, 2, 'direksi');

        $cut_off_payable = new CutOffPayable;
        $cut_off_payable->formulir_id = $formulir->id;
        $cut_off_payable->save();

        $cut_off_payable_detail = new CutOffPayableDetail;
        $cut_off_payable_detail->cut_off_payable_id = $cut_off_payable->id;
        $cut_off_payable_detail->coa_id = 27; // utang direksi 214.01
        $cut_off_payable_detail->subledger_id = $person->id;
        $cut_off_payable_detail->subledger_type = get_class(new Person());
        $cut_off_payable_detail->amount = 4346996143.30;
        $cut_off_payable_detail->notes = $formulir->notes;
        $cut_off_payable_detail->save();
    }

    public static function personCreate($type, $group, $person_name)
    {
        $person = Person::where('name', $person_name)->first();
        if ($person) {
            return $person;
        }

        $person = new Person;
        $person->person_type_id = $type;
        $person->person_group_id = $group;
        $person->created_by = 2;
        $person->updated_by = 2;
        $person->save();
        $person->code = 'EMP-'.$person->id;
        $person->name = $person_name;
        $person->save();

        return $person;
    }
}
