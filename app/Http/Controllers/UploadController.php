<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
//use App\Http\Requests\Request;
use App\Http\Requests\UploadRequest;
use App\UserImage;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    /**
     * Index
     *
     * @return string
     */
    public function index(UploadRequest $request) {
        // Delete previous uploads
//        \Auth::user()->uploads()->delete();

        // Save file
        $file = $request->file('file');
        $image = Image::make($file);

        if($image->width() > $request->get('width', \Config::get('openratio.image_temp_resize'))) {
            $image->resize($request->get('width', \Config::get('openratio.image_temp_resize')), null, function($constraint) {
                $constraint->aspectRatio();
            });
        }


//        $upload = \Auth::user()->uploads()->create([
//                'content' => (binary)$image->encode($file->getClientOriginalExtension())
//        ]);

        // Set session
        \Session::put('upload', array(
            'content' => (string)$image->encode('data-url'),
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension())
        ));

        
            if(!empty($file)){

                $date = date_create();
                $destinationPath = 'uploads/'.\Auth::user()->id; // upload path
                //$extension = $file->getClientOriginalExtension(); // getting image extension
                $name = $file->getClientOriginalName();
                $fileName = date_format($date, 'U')."-".$name;

                $fileName = (str_replace(" ","-",$fileName));
                $fileName = (str_replace("/","-",$fileName));
                $fileName = (str_replace("&","-",$fileName));
                $fileName = (str_replace(",","-",$fileName));

                if ($file->move($destinationPath, $fileName)) {
                    UserImage::create(['user_id'=>\Auth::user()->id, 'image_name'=>$fileName,'image_size'=>$file->getClientSize(),'image_type'=>$file->getClientMimeType(),'image_path'=>$destinationPath."/".$fileName]);
                    return (string)$image->encode('data-url');
                }

            }

    }
}
