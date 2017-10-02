<?php

namespace Point\Core\Http\Controllers;

class DownloadFileController extends Controller
{
    public function download($project, $folder, $name)
    {
    	$path = public_path('app/'.$project.'/'.$folder.'/' . $name);
        if (file_exists($path)) { 
            return \Response::download($path);
        }
    }
}