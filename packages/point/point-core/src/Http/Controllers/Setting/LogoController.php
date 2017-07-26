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
            $image = $request->file('logo');
            $filename = 'logo.png';
            $path = $request->project->url.'/logo/'.$filename;

            $img = \Image::make($image->getRealPath());
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();                 
            });
            $img->stream();

            \Storage::disk('local')->put($path, $img, 'public');

        	gritter_success('Success upload company logo');
            return redirect()->back();
       }
    }
}
