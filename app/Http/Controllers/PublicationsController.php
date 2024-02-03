<?php

namespace App\Http\Controllers;
use App\Mail\contac_customs;
use App\Mail\Contact;
use App\Models\PublicationsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicationsController extends Controller
{
     public function addpublication(Request $request){
        $request->validate([
            "image" => "required|image",
            "title" => "required",
            "content"=> "required",
            "auteur"=> "required",
            "image"=> "required",
        ]);

        $image = UtilController::uploadImageUrl($request->image, '/uploads/publications/');
        PublicationsModel::create([
            "image" => $image,
            "title" => $request->title,
            "content"=>$request->content,
            "auteur"=> $request->auteur,
            "legend"=> $request->legend,
        ]);
        return response()->json([
            "message" => 'Liste des publications',
             "data" => PublicationsModel::orderby('created_at','desc')->get()
        ], 200);
     }
     public function getpublication(){
        return response()->json([
            "message" => 'Liste des publications',
             "data" => PublicationsModel::orderby('created_at','desc')->get(),
             "code" => 200,
        ], 200);
     }
     
     public function contact(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'phone' => 'required',
            'name' => 'required',
            'content' => 'required',
        ]);
        Mail::to($request->email)->send(new contac_customs(env('MAIL_FROM_ADDRESS')));
        Mail::to(env('MAIL_FROM_ADDRESS'))->send(new Contact($request->email, $request->name, $request->phone, $request->content));
        return response()->json([
            "message" => "Votre message à été envoyer avec succès.",
            "code" => 200,
        ], 200);
    }
}
