<?php

namespace Point\Framework\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\ImportHelper;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\PersonGroup;

class ContactImportController extends Controller
{
    use ValidationTrait;

    public function index($type)
    {
        $view = view()->make('framework::app.master.contact.import');
        $view->person_type = PersonHelper::getType($type);
        $view->list_import = TempDataHelper::getPagination('contact.import.success.'.$view->person_type->slug, auth()->user()->id);
        $view->success = TempDataHelper::get('contact.import.success.'.$view->person_type->slug, auth()->user()->id, ['is_pagination' => true]);
        $view->error = TempDataHelper::get('contact.import.error.'.$view->person_type->slug, auth()->user()->id, ['is_pagination' => true]);
        $view->url_download = url('master/contact/'.$view->person_type->slug.'/import/download');
        $view->url_upload = url('master/contact/'.$view->person_type->slug.'/import/upload');
        $view->url_import = url('master/contact/'.$view->person_type->slug.'/import');
        return $view;
    }

    public function download($type)
    {
        \Excel::create($type, function ($excel) use ($type) {
            # Sheet Contact Import
            $excel->sheet('Contact Data', function ($sheet) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                    'C' => 25,
                    'D' => 25,
                    'E' => 25,
                    'F' => 25,
                    'G' => 25,
                ));
                $array = array(
                    array('NO', 'GROUP', 'NAME', 'EMAIL', 'ADDRESS', 'PHONE', 'NOTES')
                );

                $sheet->fromArray($array, null, 'A1', false, false);
            });

            # Sheet Master Group
            $excel->sheet('Master Group', function ($sheet) use ($type) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                ));

                $list_group = PersonHelper::getGroupByType($type);
                $array_group = array(array('NO', 'NAME'));
                for ($i = 0; $i < count($list_group); $i++) {
                    array_push($array_group, [$i + 1, $list_group[$i]['name']]);
                }
                $sheet->fromArray($array_group, null, 'A1', false, false);
            });
        })->export('xls');
        
        return redirect()->back();
    }

    public function upload(Request $request, $type)
    {
        ImportHelper::xlsValidate();
        try {
            $filePath = $request->project->url . '/contact/';
            $fileName = auth()->user()->id . '' . date('Y-m-d_His') . '.xls';
            $fileLink = $filePath . $fileName;
            if (app('request')->hasFile('file')) {
                \Storage::put($fileLink, file_get_contents($request->file('file')));
            }
            $request = $request->input();
            self::checkingIndexArray($request, $fileLink, $type);
            \Queue::push(function ($job) use ($fileLink, $request, $type) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Excel::selectSheets('Contact Data')->load('storage/app/' . $fileLink, function ($reader) use ($request, $type) {
                    $results = $reader->get()->toArray();

                    TempDataHelper::clear('contact.import.error.'.$type, $request['user']->id);
                    TempDataHelper::clear('contact.import.success.'.$type, $request['user']->id);

                    foreach ($results as $data) {
                        $group = PersonGroup::where('name', $data['group'])->first();

                        # Check if warehouse, unit and category match in database
                        $temp_name = 'contact.import.error.'.$type;
                        if ($group && !empty($data['name'])) {
                            $temp_name = 'contact.import.success.'.$type;
                        }

                        $temp = new Temp;
                        $temp->name = $temp_name;
                        $temp->user_id = $request['user']->id;
                        $temp->keys = serialize([
                            'group' => $data['group'],
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'address' => $data['address'],
                            'phone' => $data['phone'],
                            'notes' => $data['notes'],
                            'type' => $type
                        ]);
                        $temp->save();
                    }
                });

                $job->delete();
            });
        } catch (\Exception $e) {
            gritter_error($e->getMessage());
            return redirect()->back();
        }

        gritter_success('upload data success, please wait a second and refresh your page');
        return redirect('master/contact/'.$type.'/import');
    }

    public function store(Request $request, $type)
    {
        $request = $request->input();
        $user_id = auth()->user()->id;
        \Queue::push(function ($job) use ($user_id, $request, $type) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            $import = TempDataHelper::get('contact.import.success.'.$type, $user_id);
            $person_type = PersonHelper::getType($type);
            for ($i = 0; $i < count($import); $i++) {
                \DB::beginTransaction();

                # initialize data
                $group = PersonGroup::where('name', $import[$i]['group'])->first();
                $person_type = PersonHelper::getType($type);
                $code = PersonHelper::getCode($person_type);
                
                $person = new Person;
                $person->person_type_id = $person_type->id;
                $person->person_group_id = $group->id;
                $person->code = $code;
                $person->name = $import[$i]['name'];
                $person->email = $import[$i]['email'];
                $person->address = $import[$i]['address'];
                $person->phone = $import[$i]['phone'];
                $person->notes = $import[$i]['notes'];
                $person->created_by = $request['user']->id;
                $person->updated_by = $request['user']->id;
                $person->save();

                TempDataHelper::remove($import[$i]['rowid']);
                \DB::commit();
            }
            $job->delete();
        });

        TempDataHelper::clear('contact.import.error.'.$type, auth()->user()->id);
        gritter_success('import contact data success, please wait a second to take a change');
        return redirect()->back();
    }

    public function checkingIndexArray($request, $fileLink, $type)
    {
        \Excel::selectSheets('Contact Data')->load('storage/app/' . $fileLink, function ($reader) use ($request) {
            $results = $reader->get()->toArray();
            foreach ($results as $data) {
                if (! array_key_exists('group', $data)) {
                    throw new PointException("COLUMN GROUP NOT FOUND");
                }

                if (! array_key_exists('name', $data)) {
                    throw new PointException("COLUMN NAME NOT FOUND");
                }

                if (! array_key_exists('email', $data)) {
                    throw new PointException("COLUMN EMAIL NOT FOUND");
                }

                if (! array_key_exists('address', $data)) {
                    throw new PointException("COLUMN ADDRESS NOT FOUND");
                }

                if (! array_key_exists('phone', $data)) {
                    throw new PointException("COLUMN PHONE NOT FOUND");
                }

                if (! array_key_exists('notes', $data)) {
                    throw new PointException("COLUMN NOTES NOT FOUND");
                }
            }
        });
    }

    public function _updateTemp(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            $response = array('status' => 'failed');
            return response()->json($response);
        }

        $temp = Temp::find($_POST['row_id']);
        $temp->name = 'contact.import.success.'.$_POST['person_type'];
        $temp->keys = serialize([
            'group' => $_POST['group'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'address' => $_POST['address'],
            'phone' => $_POST['phone'],
            'notes' => $_POST['notes'],
            'type' => $_POST['person_type'],

        ]);
        $temp->save();
        $response = array(
            'status' => 'success',
            'group' => $_POST['group'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'address' => $_POST['address'],
            'phone' => $_POST['phone'],
            'notes' => $_POST['notes'],
        );

        return response()->json($response);
    }

    public function clearTemp($type)
    {
        TempDataHelper::clear('contact.import.success.'.$type, auth()->user()->id);
        TempDataHelper::clear('contact.import.error.'.$type, auth()->user()->id);
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function clearErrorTemp($type)
    {
        TempDataHelper::clear('contact.import.error.'.$type, auth()->user()->id);
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function deleteRow($type, $id)
    {
        TempDataHelper::remove($id);
        gritter_success('delete success');
        return redirect()->back();
    }
}
