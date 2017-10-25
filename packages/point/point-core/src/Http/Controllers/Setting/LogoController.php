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
        if (\Input::file()) {
            $image = $request->file('logo');
            $filename = 'logo.png';
            if (! is_dir(public_path('app/'.$request->project->url))) {
                mkdir(public_path('app/'.$request->project->url), 0777);
            }

            if (! is_dir(public_path('app/'.$request->project->url.'/logo'))) {
                mkdir(public_path('app/'.$request->project->url.'/logo'), 0777);
            }

            $path = public_path('app/'.$request->project->url.'/logo/');
            $img = \Image::make($image->getRealPath());
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($path.'/'.$filename);

            gritter_success('Success upload company logo');
            return redirect()->back();
        }
    }
}
