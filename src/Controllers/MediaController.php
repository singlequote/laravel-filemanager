<?php

namespace SingleQuote\FileManager\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Image;

class MediaController extends Controller
{

    /**
     * Return file if it exists. Return default image if not
     *
     * @param type $file
     * @return reponse type file
     * @author Wim Pruiksma <wim@acfbentveld.nl>
     */
    public function getFile(Request $request, $file=false)
    {
        $this->request = $request;
        if(!$file || !Storage::disk(config('laravel-filemanager.disk'))->exists($file)){
            $file = 'default.png';
        }
        $path = Storage::disk(config('laravel-filemanager.disk'))->path($file);
        $extension = strtolower(\File::extension($path));
        $explode = explode('/', $file);
        $filename = end($explode);
        return $this->returnType($extension, $path,$filename);
    }

    /**
     * Return the given type
     *
     * @param type $extension
     * @param type $path
     * @param type $filename
     * @return function
     * @author Wim Pruiksma <wim@acfbentveld.nl>
     */
    private function returnType($extension, $path, $filename)
    {
        switch($extension){
            case 'pdf':
                return $this->returnPdf($filename, $path);
            default :
                return $this->returnImage($path);
        }
    }

    /**
     *
     * @param type $extension
     * @param type $path
     * @param type $filename
     * @return file response
     * @author Wim Pruiksma <wim@acfbentveld.nl>
     */
    private function returnImage($path)
    {
        ini_set('memory_limit','256M');
        $image = Image::make($path);
        $width = $this->request->get('w', $image->width());
        $height = $this->request->get('h', $image->height());
        $quality = $this->request->get('q', 100);
        $image->fit($width, $height)->encode(null, $quality);
        return $image->response();
    }

    /**
     * Return pdf
     *
     * @param type $filename
     * @param type $path
     * @return response
     * @author Wim Pruiksma <wim@acfbentveld.nl>
     */
    private function returnPdf($filename,$path)
    {
        if($this->request->has('preview')){
            return response()->file($path);
        }
        $img = \Image::make(storage_path('app/media/files/extensions/pdf.png')); //your image I assume you have in public directory
        $img->text($filename, 250, 200, function($font){
            $font->file(public_path('fonts/OpenSans-Regular.ttf'));
            $font->size(30);
            $font->color('#00000');
            $font->align('center');
            $font->angle(45);
        });
        $img->save(storage_path('app/media/files/extensions/pdf-build.png')); //save created image (will override old image)
        return response()->file(storage_path('app/media/files/extensions/pdf-build.png'));
    }

}


