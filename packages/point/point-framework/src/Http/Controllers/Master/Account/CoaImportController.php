<?php

namespace Point\Framework\Http\Controllers\Master\Account;

use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Coa as Coa;
use Point\Framework\Models\Master\CoaCashFlow as CoaCashFlow;
use Point\Framework\Models\Master\CoaCategory as CoaCategory;
use Point\Framework\Models\Master\CoaGroup as CoaGroup;
use Point\Framework\Models\Master\CoaGroupCategory as CoaGroupCategory;
use Point\Framework\Models\Master\CoaPosition as CoaPosition;

class CoaImportController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        $results = \TempDataHelper::get('coa.import', auth()->user()->id, ['is_pagination' => true]);

        $view = view()->make('framework::app.master.coa.account.import');
        if ($results) {
            $view->results = $results;
        }
        return $view;
    }

    public function store()
    {
        \DB::beginTransaction();
        $import = \TempDataHelper::get('coa.import', auth()->user()->id, ['is_pagination' => true]);
        $position = null;
        $group_category = null;
        $category = null;
        $group = null;
        for ($i = 0; $i < count($import); $i++) {
            try {
                if ($import[$i]['position'] != '') {
                    $coa = new CoaPosition;
                    $coa->nomer = $import[$i]['position'];
                    $coa->name = $import[$i]['name'];
                    $coa->position = $import[$i]['posisi'];
                    $coa->created_by = auth()->user()->id;
                    $coa->updated_by = auth()->user()->id;
                    if (!$coa->save()) {
                        gritter_error('Import Data Failed');
                        \DB::rollback();
                        return redirect()->back();
                    }
                    $position = $coa->id;
                    $group_category = null;
                    $category = null;
                    $group = null;
                } elseif ($import[$i]['group_category'] != '') {
                    $coa = new CoaGroupCategory;
                    $coa->coa_position_id = $position;
                    $coa->nomer = $import[$i]['group_category'];
                    $coa->name = $import[$i]['name'];
                    $coa->last = $import[$i]['last'] == '' ? false : true;
                    $coa->created_by = auth()->user()->id;
                    $coa->updated_by = auth()->user()->id;
                    if (!$coa->save()) {
                        gritter_error('Import Data Failed');
                        \DB::rollback();
                        return redirect()->back();
                    }
                    $group_category = $coa->id;
                    $category = null;
                    $group = null;

                    if ($coa->last) {
                        $this->addLastCoa($position, $group_category, $category, $group, $coa->nomer, $coa->name, $import[$i]['arus_cash_flow']);
                    }
                } elseif ($import[$i]['category'] != '') {
                    $coa = new CoaCategory;
                    $coa->coa_group_category_id = $group_category;
                    $coa->nomer = $import[$i]['category'];
                    $coa->name = $import[$i]['name'];
                    $coa->last = $import[$i]['last'] == '' ? false : true;
                    $coa->created_by = auth()->user()->id;
                    $coa->updated_by = auth()->user()->id;
                    if (!$coa->save()) {
                        gritter_error('Import Data Failed');
                        \DB::rollback();
                        return redirect()->back();
                    }
                    $category = $coa->id;
                    $group = null;

                    if ($coa->last) {
                        $this->addLastCoa($position, $group_category, $category, $group, $coa->nomer, $coa->name, $import[$i]['arus_cash_flow']);
                    }
                } elseif ($import[$i]['group'] != '') {
                    $coa = new CoaGroup;
                    $coa->coa_category_id = $category;
                    $coa->nomer = $import[$i]['group'];
                    $coa->name = $import[$i]['name'];
                    $coa->last = $import[$i]['last'] == '' ? false : true;
                    $coa->created_by = auth()->user()->id;
                    $coa->updated_by = auth()->user()->id;
                    if (!$coa->save()) {
                        gritter_error('Import Data Failed');
                        \DB::rollback();
                        return redirect()->back();
                    }
                    $group = $coa->id;

                    if ($coa->last) {
                        $this->addLastCoa($position, $group_category, $category, $group, $coa->nomer, $coa->name, $import[$i]['arus_cash_flow']);
                    }
                } elseif ($import[$i]['level5'] != '') {
                    $coa = new Coa;
                    $coa->coa_position_id = $position;
                    $coa->coa_group_category_id = $group_category;
                    $coa->coa_category_id = $category;
                    $coa->coa_group_id = $group;

                    $coa_cash_flow = CoaCashFlow::where('name', '=', $import[$i]['arus_cash_flow'])->first();
                    if ($import[$i]['arus_cash_flow'] == '') {
                        $coa->coa_cash_flow_id = null;
                    } elseif (!$coa_cash_flow) {
                        $coa_cash_flow = new CoaCashFlow;
                        $coa_cash_flow->name = $import[$i]['arus_cash_flow'];
                        $coa_cash_flow->created_by = auth()->user()->id;
                        $coa_cash_flow->updated_by = auth()->user()->id;
                        $coa_cash_flow->save();
                        $coa->coa_cash_flow_id = $coa_cash_flow->id;
                    } else {
                        $coa->coa_cash_flow_id = $coa_cash_flow->id;
                    }

                    $coa->nomer = $import[$i]['level5'];
                    $coa->name = $import[$i]['name'];
                    $coa->created_by = auth()->user()->id;
                    $coa->updated_by = auth()->user()->id;
                    if (!$coa->save()) {
                        gritter_error('Import Data Failed');
                        \DB::rollback();
                        return redirect()->back();
                    }
                }
            } catch (\Exception $e) {
                // gritter_error(substr($e->getMessage(), 0, 1100).'...');
                gritter_error($e->getMessage());
                gritter_error('Import Data Failed');
                \DB::rollback();
                return redirect()->back();
            }
        }

        gritter_success('Import Data Success');
        \DB::commit();
        \TempDataHelper::clear('coa.import', auth()->user()->id);
        return redirect()->back();
    }

    private function addLastCoa($position, $group_category, $category, $group, $level5, $name, $arus_cash_flow)
    {
        $coa = new Coa;
        $coa->coa_position_id = $position;
        $coa->coa_group_category_id = $group_category;
        $coa->coa_category_id = $category;
        $coa->coa_group_id = $group;

        $coa_cash_flow = CoaCashFlow::where('name', '=', $arus_cash_flow)->first();
        if ($arus_cash_flow == '') {
            $coa->coa_cash_flow_id = null;
        } elseif (!$coa_cash_flow) {
            $coa_cash_flow = new CoaCashFlow;
            $coa_cash_flow->name = $arus_cash_flow;
            $coa_cash_flow->created_by = auth()->user()->id;
            $coa_cash_flow->updated_by = auth()->user()->id;
            $coa_cash_flow->save();
            $coa->coa_cash_flow_id = $coa_cash_flow->id;
        } else {
            $coa->coa_cash_flow_id = $coa_cash_flow->id;
        }

        $coa->nomer = $level5;
        $coa->name = $name;
        $coa->created_by = auth()->user()->id;
        $coa->updated_by = auth()->user()->id;
        if (!$coa->save()) {
            gritter_error('Import Data Failed');
            \DB::rollback();
            return redirect()->back();
        }
    }

    public function clear()
    {
        // check if its our form
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        // check authenticate user
        if (!$this->validatePassword(auth()->user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        // delete coa data
        \TempDataHelper::clear('coa.import', auth()->user()->id);

        gritter_success('Clear Data Success');

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Clear Data Success',
            'redirect' => url('master/coa/account/import')
        );
        return response()->json($response);
    }

    public function download()
    {
        \Excel::create('Coa', function ($excel) {
            $excel->sheet('Coa Data', function ($sheet) {
                $array = array(array('NO', 'LAST', 'POSISI (D/K)', 'LEVEL1', 'LEVEL2', 'LEVEL3', 'LEVEL4', 'LEVEL5', 'ARUS KAS', 'NAME', 'NOTES'));
                for ($i = 1; $i <= 1000; $i++) {
                    array_push($array, [$i, '', '', '', '', '', '', '', '', '', '', '']);
                }
                $sheet->fromArray($array, null, 'A1', false, false);
            });
        })->export('xls');
        return redirect()->back();
    }

    public function upload()
    {
        try {
            $filePath = 'uploads/import/coa/';
            $fileName = '[coa] [' . auth()->user()->id . '] ' . date('Y-m-d_His') . '.xls';
            $fileLink = $filePath . $fileName;
            if (\Request::hasFile('file')) {
                \Request::file('file')->move($filePath, $fileName);
            }

            \Excel::load($fileLink, function ($reader) use ($fileLink) {
                $results = $reader->get();
                foreach ($results->slice(0, 1000) as $data) {
                    // check required form
                    if ($data['name'] != '') {
                        // store it in DB
                        $temp = new Temp;
                        $temp->name = 'coa.import';
                        $temp->keys = serialize([
                            'posisi' => $data['posisi_dk'],
                            'last' => $data['last'],
                            'position' => $data['position'],
                            'group_category' => $data['group_category'],
                            'category' => $data['category'],
                            'group' => $data['group'],
                            'level5' => $data['level5'],
                            'arus_cash_flow' => $data['arus_cash_flow'],
                            'name' => $data['name'],
                            'notes' => $data['notes'],
                            'file_link' => $fileLink
                        ]);
                        $temp->save();
                    }
                }
            });
        } catch (\Exception $e) {
            gritter_error($e->getMessage());
            return redirect()->back();
        }

        gritter_success('Upload Data Success');

        return redirect('master/coa/account/import');
    }
}
