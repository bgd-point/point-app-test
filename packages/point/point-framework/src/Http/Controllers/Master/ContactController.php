<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\NumberHelper;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\AccessHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\PersonBank;
use Point\Framework\Models\Master\PersonContact;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;

class ContactController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param $person_type_slug
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $person_type_slug)
    {
        access_is_allowed('read.' . $person_type_slug);
        $person_type = PersonHelper::getType($person_type_slug);
        $view = view('framework::app.master.contact.index');
        $view->person_type = $person_type;
        \Log::info('person: ' . $person_type_slug . ' - ' . $person_type);
        $view->list_contact = Person::searchByType($person_type->id, $request->input('status'),$request->input('search'))->search($request->input('search'))->paginate(100);

        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $person_type_slug
     * @return \Illuminate\Http\Response
     */
    public function create($person_type_slug)
    {
        access_is_allowed('create.' . $person_type_slug);

        $person_type = PersonHelper::getType($person_type_slug);

        $view = view('framework::app.master.contact.create');
        $view->person_type = $person_type;
        $view->code = PersonHelper::getCode($person_type);

        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $person_type_slug
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $person_type_slug)
    {
        access_is_allowed('create.' . $person_type_slug);
        $person_type = PersonHelper::getType($person_type_slug);

        DB::beginTransaction();

        $this->validate($request, [
            'person_group_id' => 'required',
            'code' => 'required|unique:person,code',
            'name' => 'required'
        ]);



        $person = new Person;
        $person->person_type_id = $person_type->id;
        $person->person_group_id = $request->input('person_group_id');
        $person->code = $request->input('code');
        $person->name = $request->input('name');
        $person->email = $request->input('email');
        $person->address = $request->input('address');
        $person->phone = $request->input('phone');
        $person->notes = $request->input('notes');
        if($request->input('credit_ceiling')) {
            $person->credit_ceiling = NumberHelper::formatDB($request->input('credit_ceiling'));
        }
        $person->created_by = auth()->user()->id;
        $person->updated_by = auth()->user()->id;

        if (!$person->save()) {
            gritter_error(trans('framework::framework/master.person.create.failed', [
                'person_type' => $person_type->name,
                'name' => $person->name]));
            return redirect()->back();
        }

        $contact_name = $request->input('contact_name');
        $contact_email = $request->input('contact_email');
        $contact_address = $request->input('contact_address');
        $contact_phone = $request->input('contact_phone');

        for ($i = 0; $i < count($contact_name); $i++) {
            if (empty($contact_name[$i])) {
                continue;
            }
            $person_contact = new PersonContact;
            $person_contact->person_id = $person->id;
            $person_contact->name = $contact_name[$i];
            $person_contact->email = $contact_email[$i];
            $person_contact->address = $contact_address[$i];
            $person_contact->phone = $contact_phone[$i];
            $person_contact->created_by = auth()->user()->id;
            $person_contact->updated_by = auth()->user()->id;
            if (!$person_contact->save()) {
                gritter_error(trans('framework::framework/master.person.contact.failed'));
                return redirect()->back();
            }
        }

        $bank_branch = $request->input('bank_branch');
        $bank_name = $request->input('bank_name');
        $bank_account_number = $request->input('bank_account_number');
        $bank_account_name = $request->input('bank_account_name');

        for ($i = 0; $i < count($bank_name); $i++) {
            if (empty($bank_name[$i])) {
                continue;
            }
            $person_bank = new PersonBank;
            $person_bank->person_id = $person->id;
            $person_bank->name = $bank_name[$i];
            $person_bank->branch = $bank_branch[$i];
            $person_bank->account_number = $bank_account_number[$i];
            $person_bank->account_name = $bank_account_name[$i];
            $person_bank->created_by = auth()->user()->id;
            $person_bank->updated_by = auth()->user()->id;
            if (!$person_bank->save()) {
                gritter_error(trans('framework::framework/master.person.bank.failed'));
                return redirect()->back();
            }
        }

        DB::commit();

        gritter_success(trans('framework::framework/master.person.create.success', [
            'person_type' => $person_type->name,
            'name' => $person->name]));

        timeline_publish('create.contact', trans('framework::framework/master.person.create.timeline', [
            'person_type' => $person_type->name,
            'name' => $person->name]));

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param $person_type_slug
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($person_type_slug, $id)
    {
        access_is_allowed('read.' . $person_type_slug);
        $person_type = PersonHelper::getType($person_type_slug);

        $view = view('framework::app.master.contact.show');
        $view->person_type = $person_type;
        $view->person = Person::find($id);
        $view->histories = History::show('person', $id);
        return $view;
    }

    public function url($id)
    {
        $person = Person::find($id);
        if (! $person) {
            throw new PointException('CONTACT NOT FOUND');
        }

        return redirect('master/contact/'.$person->type->slug.'/'.$id);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param $person_type_slug
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($person_type_slug, $id)
    {
        access_is_allowed('update.' . $person_type_slug);
        $person_type = PersonHelper::getType($person_type_slug);

        $view = view('framework::app.master.contact.edit');
        $view->person_type = $person_type;
        $view->person = Person::find($id);
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $person_type_slug
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $person_type_slug, $id)
    {
        access_is_allowed('update.' . $person_type_slug);
        $person_type = PersonHelper::getType($person_type_slug);

        DB::beginTransaction();
        $person = Person::find($id);
        $person->person_group_id = $request->input('person_group_id');
        $person->name = $request->input('name');
        $person->email = $request->input('email');
        $person->address = $request->input('address');
        $person->phone = $request->input('phone');
        $person->notes = $request->input('notes');
        if($request->input('credit_ceiling')) {
            $person->credit_ceiling = NumberHelper::formatDB($request->input('credit_ceiling'));
        }

        $person->updated_by = auth()->user()->id;

        if (!$person->save()) {
            gritter_error(trans('framework::framework/master.person.update.failed'));
            return redirect()->back();
        }

        $contact_id_old = $request->input('contact_id_old');
        $contact_name_old = $request->input('contact_name_old');
        $contact_email_old = $request->input('contact_email_old');
        $contact_address_old = $request->input('contact_address_old');
        $contact_phone_old = $request->input('contact_phone_old');

        foreach ($person->contacts as $contact) {
            if (!$contact_id_old) {
                $person_contact = PersonContact::find($contact->id);
                if (!$person_contact->delete()) {
                    gritter_error(trans('framework::framework/master.person.contact.failed'));
                    return redirect()->back();
                }
            } else {
                if (in_array($contact->id, $contact_id_old)) {
                    $index = array_search($contact->id, $contact_id_old);
                    $person_contact = PersonContact::find($contact->id);
                    $person_contact->person_id = $person->id;
                    $person_contact->name = $contact_name_old[$index];
                    $person_contact->email = $contact_email_old[$index];
                    $person_contact->address = $contact_address_old[$index];
                    $person_contact->phone = $contact_phone_old[$index];
                    $person_contact->updated_by = auth()->user()->id;
                    if (!$person_contact->save()) {
                        gritter_error(trans('framework::framework/master.person.contact.failed'));
                        return redirect()->back();
                    }
                } else {
                    $person_contact = PersonContact::find($contact->id);
                    if (!$person_contact->delete()) {
                        gritter_error(trans('framework::framework/master.person.contact.failed'));
                        return redirect()->back();
                    }
                }
            }
        }

        $bank_id_old = $request->input('bank_id_old');
        $bank_branch_old = $request->input('bank_branch_old');
        $bank_name_old = $request->input('bank_name_old');
        $bank_account_number_old = $request->input('bank_account_number_old');
        $bank_account_name_old = $request->input('bank_account_name_old');

        foreach ($person->banks as $bank) {
            if (!$bank_id_old) {
                $person_bank = PersonBank::find($bank->id);
                if (!$person_bank->delete()) {
                    gritter_error(trans('framework::framework/master.person.bank.failed'));
                    return redirect()->back();
                }
            } else {
                if (in_array($bank->id, $bank_id_old)) {
                    $index = array_search($bank->id, $bank_id_old);
                    $person_bank = PersonBank::find($bank->id);
                    $person_bank->person_id = $person->id;
                    $person_bank->branch = $bank_branch_old[$index];
                    $person_bank->name = $bank_name_old[$index];
                    $person_bank->account_number = $bank_account_number_old[$index];
                    $person_bank->account_name = $bank_account_name_old[$index];
                    $person_bank->updated_by = auth()->user()->id;
                    if (!$person_bank->save()) {
                        gritter_error(trans('framework::framework/master.person.bank.failed'));
                        return redirect()->back();
                    }
                } else {
                    $person_bank = PersonBank::find($bank->id);
                    if (!$person_bank->delete()) {
                        gritter_error(trans('framework::framework/master.person.bank.failed'));
                        return redirect()->back();
                    }
                }
            }
        }

        $contact_name = $request->input('contact_name');
        $contact_email = $request->input('contact_email');
        $contact_address = $request->input('contact_address');
        $contact_phone = $request->input('contact_phone');

        for ($i = 0; $i < count($contact_name); $i++) {
            $person_contact = new PersonContact;
            $person_contact->person_id = $person->id;
            $person_contact->name = $contact_name[$i];
            $person_contact->email = $contact_email[$i];
            $person_contact->address = $contact_address[$i];
            $person_contact->phone = $contact_phone[$i];
            $person_contact->created_by = auth()->user()->id;
            $person_contact->updated_by = auth()->user()->id;
            if (!$person_contact->save()) {
                gritter_error(trans('framework::framework/master.person.bank.failed'));
                return redirect()->back();
            }
        }

        $bank_branch = $request->input('bank_branch');
        $bank_name = $request->input('bank_name');
        $bank_account_number = $request->input('bank_account_number');
        $bank_account_name = $request->input('bank_account_name');

        for ($i = 0; $i < count($bank_name); $i++) {
            $person_bank = new PersonBank;
            $person_bank->person_id = $person->id;
            $person_bank->name = $bank_name[$i];
            $person_bank->branch = $bank_branch[$i];
            $person_bank->account_number = $bank_account_number[$i];
            $person_bank->account_name = $bank_account_name[$i];
            $person_bank->created_by = auth()->user()->id;
            $person_bank->updated_by = auth()->user()->id;
            if (!$person_bank->save()) {
                gritter_error(trans('framework::framework/master.person.bank.failed'));
                return redirect()->back();
            }
        }

        DB::commit();

        gritter_success(trans('framework::framework/master.person.update.success', [
            'person_type' => $person_type->name,
            'name' => $person->name]));

        timeline_publish('update.contact', trans('framework::framework/master.person.update.timeline', [
            'person_type' => $person_type->name,
            'name' => $person->name]));

        return redirect('master/contact/' . $person_type->slug . '/' . $person->id);
    }

    /**
     * Delete contact
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $person_type_slug)
    {
        $redirect = false;

        if ($request->input('redirect')) {
            $redirect = $request->input('redirect');
        }

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(auth()->user()->name, $request->input('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        try {
            DB::beginTransaction();
            $person = Person::find($request->input('id'));
            $person->delete();

            timeline_publish('delete.contact', trans('framework::framework/master.person.delete.timeline', [
                'person_type' => $person->type->name,
                'name' => $person->name]));

            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Delete Success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success(trans('framework::framework/master.person.delete.success', [
                'person_type' => $person->type->name,
                'name' => $person->name]));
        }

        return $response;
    }

    public function _insert(Request $request, $person_type)
    {
        AccessHelper::isAllowed('create.' . $person_type);

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        DB::beginTransaction();
        $validator = \Validator::make($request->all(), [
            'code' => 'required|unique:person,code',
            'name' => 'required',
            'person_group_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed']);
        }

        $person_type = PersonHelper::getType($person_type);

        $person = new Person;
        $person->person_type_id = $person_type->id;
        $person->person_group_id = $request->input('person_group_id');
        $person->code = $request->input('code');
        $person->name = $request->input('name');
        $person->created_by = auth()->user()->id;
        $person->updated_by = auth()->user()->id;

        if (!$person->save()) {
            return response()->json(['status' => 'failed']);
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'id' => $person->id,
            'name' => $person->codeName,
            'code' => PersonHelper::getCode($person_type)
        ]);
    }

    public function _listGroup()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }
        $slug = app('request')->input('slug');
        $person_type = PersonType::where('slug', $slug)->first();
        $list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $code_contact = PersonHelper::getCode($person_type);


        $groups = [];
        foreach ($list_group as $group) {
            array_push($groups, ['text' => $group->name, 'value' => $group->id]);
            $defaultId = $group->id;
        }

        $response = array(
            'lists' => $groups,
            'code' => $code_contact,
            'defaultId' => $defaultId,
        );
        return response()->json($response);
    }

    public function _state(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!auth()->user()->may('update.' . $request->input('slug'))) {
            $response = array('status' => 'failed', 'message' => 'permission denied');
            return response()->json($response);
        }

        $person = Person::find($request->input('index'));

        if (!$person) {
            $response = array('status' => 'failed', 'message' => $request->input('index'));
            return response()->json($response);
        }

        $person->disabled = $person->disabled == 0 ? 1 : 0;
        $person->save();

        $response = array('status' => 'success', 'message' => 'update data finished', 'data_value' => $person->disabled);

        return response()->json($response);
    }


    /**
     * List all person from all type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function _list()
    {
        $persons = Person::active()->get();
        $list_person = [];
        foreach ($persons as $person) {
            array_push($list_person, ['text' => $person->codeName, 'value' => $person->id]);
        }
        $response = array(
            'lists' => $list_person
        );
        return response()->json($response);
    }

    /**
     * List person from specific type
     *
     * @param $slug
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function _listByType($slug)
    {
        $persons = PersonHelper::getByType([$slug]);
        $list_person = [];
        foreach ($persons as $person) {
            array_push($list_person, ['text' => $person->codeName, 'value' => $person->id]);
        }
        $response = array(
            'lists' => $list_person
        );
        return response()->json($response);
    }
}
