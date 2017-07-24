<?php

namespace Point\Core\Helpers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * upload to storage
     * @param $file
     * @param $location
     * @param $name
     */
    public static function upload($file, $location, $name)
    {
        $name = static::getClientStorageLocation() . $location . $name;
        Storage::put($name, file_get_contents($file));
    }

    private static function getClientStorageLocation()
    {
        return 'client/' . config('point.client.slug') . '/';
    }

    /**
     * download from storage
     * @param $location
     * @return mixed
     */
    public static function download($location)
    {
        $path = static::getClientStorageLocation() . '/' . $location;

        $fs = Storage::getDriver();
        $stream = $fs->readStream($path);
        return Response::stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => $fs->getMimetype($path),
            "Content-Length" => $fs->getSize($path),
            "Content-disposition" => "inline; filename=\"" . basename($path) . "\"",
        ]);
    }

    /**
     * delete from storage
     * @param $location
     */
    public static function delete($location)
    {
        $path = static::getClientStorageLocation() . '/' . $location;
        Storage::delete($path);
    }
}
