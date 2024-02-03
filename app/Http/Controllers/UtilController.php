<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;


class UtilController extends Controller
{

      public static function uploadImageUrl($field, $destination)
    {
        if ($field) {
            $image = Image::make($field);
            $png_url = md5(rand(1000, 10000)) . ".png";
            $width = $image->width();
            $height = $image->height();
            $image->resize($width / 2, $height / 2); // Redimensionnement de l'image à 120 x 80 px
            $image->save(public_path().$destination . $png_url);
            return env('APP_URL').$destination.$png_url;
        }
    }

    public static function uploadMultipleImage($field, $destination)
    {
        $images = [];
        if ($field) {
            foreach ($field as $file) {
                $image = Image::make($file);
                $png_url = md5(rand(1000, 10000)) . ".png";
                $width = $image->width();
                $height = $image->height();
                //$image->resize($width / 2, $height / 2); // Redimensionnement de l'image à 120 x 80 px
                $image->save(public_path() . $destination . $png_url);
                array_push($images, env('APP_URL').$destination.$png_url);
            }
            return $images;
        }
    }
    public static function removeImage($field, $destination)
    {
       if (File::exists(public_path($destination . $field))) {
            File::delete(public_path($destination . $field));
        }
    }
}
