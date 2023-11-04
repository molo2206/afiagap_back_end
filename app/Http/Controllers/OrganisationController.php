<?php

namespace App\Http\Controllers;

use App\Mail\Creationorg;
use App\Models\airesante;
use App\Models\indicateur;
use App\Models\org_indicateur;
use App\Models\Organisation;
use App\Models\typeorg;
use App\Models\zonesante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrganisationController extends Controller
{
    public  function AddOrg(Request $request)
    {
        $request->validate([
            "name"  => "required|string",
            "email" => "required|email",
            "phone" => "required|string",
            "description" => "required|string",
            "adresse" => "required|string",
            "sigle" => "required|string",
            "pointfocal" => "required|string",
            "typeorgid" => "required|string",
        ]);
        if(typeorg::find($request->typeorgid)){
        if (!Organisation::where('email', $request->email)->exists()) {
            if (!Organisation::where('phone', $request->phone)->exists()) {
                if (!Organisation::where('name', $request->name)->exists()) {
                    Organisation::create([
                        "name"  => $request->name, "email"  => $request->email,
                        "phone" => $request->phone, "description"  => $request->description,
                        "logo"  => 'https://apiafiagap.cosamed.org/public/uploads/organ/org.png', "adresse"  => $request->adresse,
                        "sigle" => $request->sigle, "activite"  => null,
                        "pointfocal"  => null,
                        "typeorgid"  => $request->typeorgid, "status" => 0,
                        "delete" => 0,
                    ]);
                    Mail::to($request->email)->send(new Creationorg($request->email, $request->name));
                    return response()->json([
                        "message" => "Votre organisation à été créée avec succès.",
                        "code" => 200,
                    ], 200);
                    return response()->json([
                        "message" => "Enregistrement avec succès!",
                        "code" => 200,
                        "data" => Organisation::orderBy ('name', 'asc')->get (),
                    ], 200);
                } else {
                    return response([
                        "message" => "Ce nom d'organisation existe dans le système!",
                        "code" => 422,
                        "data" => null,
                    ], 422);
                }
            } else {
                return response([
                    "message" => "Ce numèro phone existe dans le système!",
                    "code" => 422,
                    "data" => null,
                ], 422);
            }
        } else {
            return response([
                "message" => "Cette adresse email existe dans le système!",
                "code" => 422,
                "data" => null,
            ], 422);
        }
        }else{
            return response([
                "message" => "Ce type d'organisation n'existe pas dans le système!",
                "code" => 422,
                "data" => null,
            ], 422);
        }

    }
    public function list_organisation(){
           return response([
                "message" => "Liste des organisations",
                "code" => 200,
                "data" => Organisation::orderBy ('name', 'asc')->get (),
           ]);
    }
    public function addindicateur(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'value' => 'required',
        ]);
        if (indicateur::where('name', $request->name)->exists()) {
            return response([
                "message" => "Cet indicateur existe déjà dans le système!",
                "code" => 422,
                "data" => null,
            ], 422);
        } else {
            $addind = indicateur::create([
                'name' => $request->name,
                'value' => $request->value,
                'status' => 0
            ]);
            return response()->json([
                "message" => "Enregistrement avec succès!",
                "code" => 200,
                "data" => indicateur::all(),
            ], 200);
        }
    }
    public function listeIndicateur()
    {
        $allindicateur = indicateur::all();
        return response()->json([
            "code" => 200,
            "message" => "Liste des indicateurs!",
            "data" => $allindicateur,

        ], 200);
    }

    public function org_indicateur(Request $request){
        $request->validate([
            "orgid"=> 'required',
            "indicateurid" => 'required',
        ]);
        if(indicateur::where('id', $request->indicateurid)->exists()){
            if(Organisation::where('id', $request->indicateurid)->exists()){
                $addorg_ind = org_indicateur::create([
                    'orgid' => $request->orgid,
                    'indicateurid' => $request->indicateurid,
                    'status' => 0,
                    'delete' => 0
                ]);
                return response()->json([
                    "message" => "Enregistrement avec succès!",
                    "code" => 200,
                    "data" => org_indicateur::all(),
                ], 200);
            }else{
                return response([
                    "message" => "Cette organisation n'existe pas dans le système!",
                    "code" => 422,
                    "data" => null,
                ]);
            }
        }else{
            return response([
                "message" => "Cette indicateur n'existe pas dans le système!",
                "code" => 422,
                "data" => null,
            ]);
        }
    }

    public function addtypeorg(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        if (typeorg::where('name', $request->name)->exists()) {
            return response([
                "message" => "Ce type d'organisation existe dans le système!",
                "code" => 422,
                "data" => null,
            ]);
        } else {
            $addtype = typeorg::create([
                'name' => $request->name,
            ]);
            return response()->json([
                "message" => "Enregistrement avec succès!",
                "code" => 200,
                "data" => typeorg::all(),
            ], 200);
        }
    }

    public function listtype()
    {
        return response()->json([
            "message" => "Liste type org",
            "code" => 200,
            "data" => typeorg::all(),
        ], 200);
    }
}
