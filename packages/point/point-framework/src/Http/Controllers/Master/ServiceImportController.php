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
use Point\Framework\Models\Master\Service;

class ServiceImportController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        $view = view()->make('framework::app.master.service.import');
        $view->list_import = TempDataHelper::getPagination('service.import.success', auth()->user()->id);
        $view->success = TempDataHelper::get('service.import.success', auth()->user()->id, ['is_pagination' => true]);
        $view->error = TempDataHelper::get('service.import.error', auth()->user()->id, ['is_pagination' => true]);
        $view->url_download = url('master/service/import/download');
        $view->url_upload = url('master/service/import/upload');
        $view->url_import = url('master/service/import');
        return $view;
    }

    public function download()
    {
        \Excel::create('Service', function ($excel) {
            # Sheet Service Import
            $excel->sheet('Service Data', function ($sheet) {
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 25,
                    'C' => 25,
                ));
                $array = array(
                    array('NO', 'NAME', 'PRICE', 'NOTES')
                );

                $sheet->fromArray($array, null, 'A1', false, false);
            });
        })->export('xls');
        
        return redirect()->back();
    }

    public function upload(Request $request)
    {
        ImportHelper::xlsValidate();
        try {
            $filePath = $request->project->url . '/service/';
            $fileName = auth()->user()->id . '' . date('Y-m-d_His') . '.xls';
            $fileLink = $filePath . $fileName;
            if (app('request')->hasFile('file')) {
                \Storage::put($fileLink, file_get_contents($request->file('file')));
            }
            $request = $request->input();
            self::checkingIndexArray($request, $fileLink);
            \Queue::push(function ($job) use ($fileLink, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Excel::selectSheets('Service Data')->load('storage/app/' . $fileLink, function ($reader) use ($request) {
                    $results = $reader->get()->toArray();

                    TempDataHelper::clear('service.import.error', $request['user']->id);
                    TempDataHelper::clear('service.import.success', $request['user']->id);

                    foreach ($results as $data) {
                        $service = Service::where('name', $data['name'])->first();
                        \Log::info($service);
                        # Check if price not set
                        $price = 0;
                        if (!empty($data['price']) && is_numeric($data['price'])) {
                            $price = number_format_db($data['price']);
                        }

                        $temp_name = 'service.import.error';
                        if (! $service && $data['name'] != '') {
                            $temp_name = 'service.import.success';
                        }

                        $temp = new Temp;
                        $temp->name = $temp_name;
                        $temp->user_id = $request['user']->id;
                        $temp->keys = serialize([
                            'name' => $data['name'],
                            'price' => $price,
                            'notes' => $data['notes']
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
        return redirect('master/service/import');
    }

    public function store(Request $request)
    {
        $request = $request->input();
        $user_id = auth()->user()->id;
        \Queue::push(function ($job) use ($user_id, $request) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            $import = TempDataHelper::get('service.import.success', $user_id);
            for ($i = 0; $i < count($import); $i++) {
                \DB::beginTransaction();
                
                $person = new Service;
                $person->name = $import[$i]['name'];
                $person->price = $import[$i]['price'];
                $person->notes = $import[$i]['notes'];
                $person->created_by = $request['user']->id;
                $person->updated_by = $request['user']->id;
                $person->save();

                TempDataHelper::remove($import[$i]['rowid']);
                \DB::commit();
            }
            $job->delete();
        });

        TempDataHelper::clear('service.import.error', auth()->user()->id);
        gritter_success('import service data success, please wait a second to take a change');
        return redirect()->back();
    }

    public function checkingIndexArray($request, $fileLink)
    {
        \Excel::selectSheets('Service Data')->load('storage/app/' . $fileLink, function ($reader) use ($request) {
            $results = $reader->get()->toArray();
            foreach ($results as $data) {
                if (! array_key_exists('name', $data)) {
                    throw new PointException("COLUMN NAME NOT FOUND");
                }

                if (! array_key_exists('price', $data)) {
                    throw new PointException("COLUMN PRICE NOT FOUND");
                }

                if (! array_key_exists('notes', $data)) {
                    throw new PointException("COLUMN notes NOT FOUND");
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
            'name' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            $response = array('status' => 'failed');
            return response()->json($response);
        }

        $temp = Temp::find($_POST['row_id']);
        $temp->name = 'service.import.success';
        $temp->keys = serialize([
            'name' => $_POST['name'],
            'price' => number_format_db($_POST['price']),
            'notes' => $_POST['notes']
        ]);
        $temp->save();

        $response = array(
            'status' => 'success',
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'notes' => $_POST['notes'],
        );

        return response()->json($response);
    }

    public function clearTemp()
    {
        TempDataHelper::clear('service.import.success', auth()->user()->id);
        TempDataHelper::clear('service.import.error', auth()->user()->id);
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function clearErrorTemp()
    {
        TempDataHelper::clear('service.import.error', auth()->user()->id);
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function deleteRow($id)
    {
        TempDataHelper::remove($id);
        gritter_success('delete success');
        return redirect()->back();
    }
}
