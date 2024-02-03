<?php

namespace App\Http\Controllers;

use App\Models\ActiviteModel;
use App\Models\AffectationModel;
use App\Models\AffectationPermission;
use App\Models\BeneficeAtteint;
use App\Models\BeneficeCible;
use App\Models\CohpModel;
use App\Models\ConsultationCliniqueMobile;
use App\Models\ConsultationExterneFosa;
use App\Models\IndicateurActivite;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiviteController extends Controller
{
    public function create_activite(Request $request)
    {
        $request->validate([
            "title_projet" => 'required',
            "provinceid" => 'required',
            "territoirid" => 'required',
            "zoneid" => 'required',
            "airid" => 'required',
            "structureid" => 'required',
            "orgid" => 'required'
        ]);

        $user = Auth::user();
        $permission = Permission::where('name', 'create_activite')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                $activite = ActiviteModel::create([
                    "title_projet" => $request->title_projet,
                    "provinceid" =>  $request->provinceid,
                    "territoirid" => $request->territoirid,
                    "zoneid" => $request->zoneid,
                    "airid" =>  $request->airid,
                    "structureid" => $request->structureid,
                    "org_make_repport" => $request->org_make_repport,
                    "org_make_oeuvre" =>  $request->org_make_oeuvre,
                    "date_rapportage" =>  $request->date_rapportage,
                    "identifiant_project" => $request->identifiant_project,
                    "typeprojetid" => $request->typeprojetid,
                    "type_intervention" => $request->type_intervention,
                    "axe_strategique" => $request->axe_strategique,
                    "odd" => $request->odd,
                    "description_activite" => $request->description_activite,
                    "statut_activite" => $request->statut_activite,
                    "modalite" => $request->modalite,
                    "src_financement" => $request->src_financement,
                    "vaccination" => $request->vaccination,
                    "malnutrition" => $request->malnutrition,
                    "remarque" => $request->remarque,
                    "date_debut_projet" => $request->date_debut_projet,
                    "date_fin_projet" => $request->date_fin_projet,
                    'cohp_relais' => $request->cohp_relais,
                    'type_reponse' => $request->type_reponse,
                    'type_benef' => $request->type_benef,
                    'phone' =>$request->phone,
                    'email' =>$request->email,
                ]);


               //INSERTION INDICATEURS
                    if ($activite) {
                        $activite->indicataire()->detach();
                        foreach ($request->indicateuractivite as $item) {
                            $activite->indicataire()->attach([$activite->id =>
                            [
                                'indicateurid' => $item,
                            ]]);
                        }
                    }

                BeneficeCible::create([
                    'activiteid' => $activite->id,
                    'homme_cible' => $request->homme_cible,
                    'femme_cible' =>  $request->femme_cible,
                    'enfant_garcon_moin_cinq' =>  $request->enfant_garcon_moin_cinq,
                    'enfant_fille_moin_cinq'  =>  $request->enfant_fille_moin_cinq,
                    'personne_cible_handicap' =>  $request->personne_cible_handicap,
                    'total_cible' =>  $request->total_cible,
                ]);

                BeneficeAtteint::create([
                    "activiteid" => $activite->id,
                    "homme_atteint" => $request->homme_atteint,
                    "femme_atteint" =>  $request->femme_atteint,
                    "enfant_garcon_moin_cinq" =>  $request->enfant_garcon_moin_cinq_atteint,
                    "enfant_fille_moin_cinq" =>  $request->enfant_fille_moin_cinq_atteint,
                    "personne_atteint_handicap" =>  $request->personne_atteint_handicap,
                    "total_atteint" => $request->total_atteint
                ]);

                ConsultationExterneFosa::create([
                    "activiteid" => $activite->id,
                    "homme_consulte_fosa" => $request->homme_consulte_fosa,
                    "femme_consulte_fosa" => $request->femme_consulte_fosa,
                    "consulte_moin_cinq_fosa" => $request->consulte_moin_cinq_fosa,
                    "consulte_cinq_plus_fosa" => $request->consulte_cinq_plus_fosa,
                ]);

                ConsultationCliniqueMobile::create([
                    "activiteid" => $activite->id,
                    "homme_consulte_mob" => $request->homme_consulte_mob,
                    "femme_consulte_mob" => $request->femme_consulte_mob,
                    "consulte_moin_cinq_mob" => $request->consulte_moin_cinq_mob,
                    "consulte_cinq_plus_mob" => $request->consulte_cinq_plus_mob,
                ]);

                return response()->json([
                    "message" => "Success",
                    "data" => ActiviteModel::with(
                        'dataprovince',
                        'dataterritoir',
                        'datazone',
                        'dataaire',
                        'datastructure',
                        'data_organisation_make_rapport.type_org',
                        'data_organisation_mise_en_oeuvre.type_org',
                        'databeneficecible',
                        'databeneficeatteint',
                        'dataconsultationexterne',
                        'dataconsultationcliniquemobile',
                        'paquetappui.indicateur'
                    )->where('org_make_repport', $request->org_make_repport)->orderBy('created_at', 'desc')->get()
                ]);
            } else {
                return response()->json([
                    "message" => "Vous ne pouvez pas éffectuer cette action",
                    "code" => 402
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "cette organisationid" . $organisation->id . "n'existe pas",
                "code" => 402
            ], 402);
        }
    }

    public function updateactivite(Request $request, $id)
    {
        $request->validate([
            "title_projet" => 'required',
            "provinceid" => 'required',
            "territoirid" => 'required',
            "zoneid" => 'required',
            "airid" => 'required',
            "structureid" => 'required',
            "orgid" => 'required'
        ]);

        $user = Auth::user();
        $permission = Permission::where('name', 'update_activite')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                $activite = ActiviteModel::where('id', $id)->first();

                $activite->title_projet = $request->title_projet;
                $activite->provinceid =  $request->provinceid;
                $activite->territoirid = $request->territoirid;
                $activite->zoneid = $request->zoneid;
                $activite->airid =  $request->airid;
                $activite->structureid = $request->structureid;
                $activite->org_make_repport = $request->org_make_repport;
                $activite->org_make_oeuvre =  $request->org_make_oeuvre;
                $activite->date_rapportage =  $request->date_rapportage;
                $activite->identifiant_project = $request->identifiant_project;
                $activite->typeprojetid = $request->typeprojetid;
                $activite->type_intervention = $request->type_intervention;
                $activite->axe_strategique = $request->axe_strategique;
                $activite->odd = $request->odd;
                $activite->description_activite = $request->description_activite;
                $activite->statut_activite = $request->statut_activite;
                $activite->modalite = $request->modalite;
                $activite->src_financement = $request->src_financement;
                $activite->vaccination = $request->vaccination;
                $activite->malnutrition = $request->malnutrition;
                $activite->remarque = $request->remarque;
                $activite->date_debut_projet = $request->date_debut_projet;
                $activite->date_fin_projet = $request->date_fin_projet;
                $activite->cohp_relais = $request->cohp_relais;
                $activite->type_reponse = $request->type_reponse;
                $activite->type_benef = $request->type_benef;
                $activite->phone = $request->phone;
                $activite->email = $request->email;
                $activite->update();

                //INSERTION INDICATEURS
                    if ($activite) {
                        $activite->indicataire()->detach();
                        foreach ($request->indicateuractivite as $item) {
                            $activite->indicataire()->attach([$activite->id =>
                            [
                                'indicateurid' => $item,
                            ]]);
                        }
                    }

                $beneficecible = BeneficeCible::where('activiteid', $activite->id)->first();
                $beneficecible->homme_cible = $request->homme_cible;
                $beneficecible->femme_cible = $request->femme_cible;
                $beneficecible->enfant_garcon_moin_cinq =  $request->enfant_garcon_moin_cinq;
                $beneficecible->enfant_fille_moin_cinq =  $request->enfant_fille_moin_cinq;
                $beneficecible->personne_cible_handicap =  $request->personne_cible_handicap;
                $beneficecible->total_cible = $request->total_cible;
                $beneficecible->save();

                $beneficeatteint = BeneficeAtteint::where('activiteid', $activite->id)->first();
                $beneficeatteint->homme_atteint = $request->homme_atteint;
                $beneficeatteint->femme_atteint =  $request->femme_atteint;
                $beneficeatteint->enfant_garcon_moin_cinq =  $request->enfant_garcon_moin_cinq_atteint;
                $beneficeatteint->enfant_fille_moin_cinq = $request->enfant_fille_moin_cinq_atteint;
                $beneficeatteint->personne_atteint_handicap =  $request->personne_atteint_handicap;
                $beneficeatteint->total_atteint = $request->htotal_atteint;
                $beneficeatteint->save();

                $consulfosa = ConsultationExterneFosa::where('activiteid', $activite->id)->first();
                $consulfosa->homme_consulte_fosa = $request->homme_consulte_fosa;
                $consulfosa->femme_consulte_fosa = $request->femme_consulte_fosa;
                $consulfosa->consulte_moin_cinq_fosa = $activite->consulte_moin_cinq_fosa;
                $consulfosa->consulte_cinq_plus_fosa = $request->consulte_cinq_plus_fosa;
                $consulfosa->save();

                $consulclini = ConsultationCliniqueMobile::where('activiteid', $activite->id)->first();
                $consulclini->homme_consulte_mob = $request->homme_consulte_mob;
                $consulclini->femme_consulte_mob = $request->femme_consulte_mob;
                $consulclini->consulte_moin_cinq_mob = $activite->consulte_moin_cinq_mob;
                $consulclini->consulte_cinq_plus_mob = $request->consulte_cinq_plus_mob;
                $consulclini->save();

                return response()->json([
                    "message" => "Modification avec succès",
                    "data" => ActiviteModel::with(
                        'dataprovince',
                        'dataterritoir',
                        'datazone',
                        'dataaire',
                        'datastructure',
                        'data_organisation_make_rapport.type_org',
                        'data_organisation_mise_en_oeuvre.type_org',
                        'databeneficecible',
                        'databeneficeatteint',
                        'dataconsultationexterne',
                        'dataconsultationcliniquemobile',
                        'paquetappui.indicateur'
                    )->where('org_make_repport', $request->org_make_repport)->orderBy('updated_at', 'desc')->where('status', 0)->where('deleted', 0)->get()
                ]);
            } else {
                return response()->json([
                    "message" => "Vous ne pouvez pas éffectuer cette action",
                    "code" => 402
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "cette organisationid" . $organisation->id . "n'existe pas",
                "code" => 402
            ], 402);
        }
    }

    public function get_activite($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_activite')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                return response()->json([
                    "message" => "Modification avec succès",
                    "data" => ActiviteModel::with(
                        'dataprovince',
                        'dataterritoir',
                        'datazone',
                        'dataaire',
                        'datastructure',
                        'data_organisation_make_rapport.type_org',
                        'data_organisation_mise_en_oeuvre.type_org',
                        'databeneficecible',
                        'databeneficeatteint',
                        'dataconsultationexterne',
                        'dataconsultationcliniquemobile',
                       'paquetappui.indicateur'
                    )->where('org_make_repport', $orgid)->orderBy('created_at', 'desc')->where('status', 0)->where('deleted', 0)->get()
                ]);
            } else {
                return response()->json([
                    "message" => "Vous ne pouvez pas éffectuer cette action",
                    "code" => 402
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "cette organisationid" . $organisation->id . "n'existe pas",
                "code" => 402
            ], 402);
        }
    }
    public function get_all_activite($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_activite')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                return response()->json([
                    "message" => "Modification avec succès",
                    "data" => ActiviteModel::with(
                        'dataprovince',
                        'dataterritoir',
                        'datazone',
                        'dataaire',
                        'datastructure',
                        'data_organisation_make_rapport.type_org',
                        'data_organisation_mise_en_oeuvre.type_org',
                        'databeneficecible',
                        'databeneficeatteint',
                        'dataconsultationexterne',
                        'dataconsultationcliniquemobile',
                        'paquetappui.indicateur'
                    )->orderBy('created_at', 'desc')->where('status', 0)->where('deleted', 0)->get()
                ]);
            } else {
                return response()->json([
                    "message" => "Vous ne pouvez pas éffectuer cette action",
                    "code" => 402
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "cette organisationid" . $organisation->id . "n'existe pas",
                "code" => 402
            ], 402);
        }
    }

    public function detailActivite($id)
    {
        return response()->json([
            "message" => "Detail d'une activité",
            "data" => ActiviteModel::with(
                'dataprovince',
                'dataterritoir',
                'datazone',
                'dataaire',
                'datastructure',
                        'data_organisation_make_rapport.type_org',
                        'data_organisation_mise_en_oeuvre.type_org',
                'databeneficecible',
                'databeneficeatteint',
                'dataconsultationexterne',
                'dataconsultationcliniquemobile',
                'paquetappui.indicateur'
            )->where('id', $id)->where('status', 0)->where('deleted', 0)->first()
        ]);
    }
    public function getcohp(){
           return response()->json([
                   "message" => "Liste des COHP_RELAIS_IMT OMS",
                   "code" => 200,
                   "data" => CohpModel::all()
           ],200);
    }
}
