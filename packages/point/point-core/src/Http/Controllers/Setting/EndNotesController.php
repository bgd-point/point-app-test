<?php

namespace Point\Core\Http\Controllers\Setting;

use Illuminate\Http\Request;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\EndNotes;
use Point\Core\Traits\ValidationTrait;

class EndNotesController extends Controller
{
    use ValidationTrait;

    /**
     * Send global notification
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $view = view('core::app.settings.end-notes');
        $view->list_end_notes = EndNotes::all();
        return $view;
    }

    public function update(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'notes' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed']);
        }
        
        \DB::beginTransaction();

        $notes = EndNotes::find($_POST['id']);
        $notes->notes = $_POST['notes'];
        
        if (!$notes->save()) {
            return response()->json(['status' => 'failed']);
        }

        \DB::commit();

        return response()->json([
            'id' => $notes->id,
            'status' => 'success',
            'notes' => $notes->notes
        ]);
    }
}
