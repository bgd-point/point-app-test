<?php

namespace Point\Core\Http\Controllers\Setting;

use Illuminate\Http\Request;
use Point\Core\Http\Controllers\Controller;

class LogoController extends Controller
{
    public function index()
    {
        return view('core::app.settings.logo');
    }

    public function store(Request $request)
    {
    	if(\Input::file())
        {
            $image = \Input::file('logo');
            $filename  = 'logo.png';
            $path = 'uploads/' .$request->project->url . '/logo/' . $filename;
        
            \Image::make($image->getRealPath())->resize(200, null, function($constraint){
            	$constraint->aspectRatio();
            })->save($path);

        	gritter_success('Success upload company logo');
            return redirect()->back();
       }
    }
}
