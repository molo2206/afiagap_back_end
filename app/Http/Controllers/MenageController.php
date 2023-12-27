<?php

namespace App\Http\Controllers;

use App\Models\AffectationModel;
use App\Models\AffectationPermission;
use App\Models\CalendrierVaccinModel;
use App\Models\CritereMenageModel;
use App\Models\CritereVulModel;
use App\Models\MenageModel;
use App\Models\Permission;
use App\Models\PersonnelModel;
use App\Models\PersonnesModel;
use App\Models\QuestionEnceinteModel;
use App\Models\ReponseEnceinteModel;
use App\Models\RoleMenageModel;
use App\Models\TypePersonneModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenageController extends Controller
{
    public function create_menage(Request $request)
    {
        $request->validate([
            "adresse_actuel" => 'required',
            "taille" => 'required',
            'habitation' => 'required',
            'origine' => 'required',
            "datacritere" => "required|array",
            'orgid' => 'required',
        ]);
        $user = Auth::user();
        $permission = Permission::where('name', 'create_menage')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        $code = mt_rand(1, 9999999999);
        $codemenage = 'MEN-' . $code;
        $datamenage = MenageModel::where('code_menage', $codemenage)->exists();

        if ($organisation) {
            if ($permission_gap) {
                if ($datamenage) {
                    $code = mt_rand(1, 9999999999);
                    $codemenage = 'MEN-' . $code;
                    $datamenage = MenageModel::create([
                        "code_menage" => $codemenage,
                        "adresse_actuel" => $request->adresse_actuel,
                        "taille" => $request->taille,
                        'habitation' => $request->habitation,
                        'origine' => $request->origine,
                        'userid' => $user->id,
                    ]);

                    foreach ($request->datacritere as $item) {
                        CritereMenageModel::create([
                            'menageid' => $datamenage->id,
                            'cretereid' => $item,
                        ]);
                    }

                    return response()->json([
                        "message" => "Traitement reussi avec succès",
                        "code" => 200,
                        "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $datamenage->id)->first()
                    ], 200);
                } else {
                    $datamenage = MenageModel::create([
                        "code_menage" => $codemenage,
                        "adresse_actuel" => $request->adresse_actuel,
                        "taille" => $request->taille,
                        'habitation' => $request->habitation,
                        'origine' => $request->origine,
                        'userid' => $user->id,
                    ]);
                    foreach ($request->datacritere as $item) {
                        CritereMenageModel::create([
                            'menageid' => $datamenage->id,
                            'cretereid' => $item,
                        ]);
                    }
                    return response()->json([
                        "message" => "Traitement reussi avec succès",
                        "code" => 200,
                        "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $datamenage->id)->first()
                    ], 200);
                }
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

 public function delete_menage(Request $request)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'create_menage')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        $datamenage = MenageModel::where('id', $request->id)->first();
        if ($organisation) {
            if ($permission_gap) {
                if ($datamenage) {
                    $datamenage->deleted = 1;
                    $datamenage->save();
                    return response()->json([
                        "message" => "Traitement reussi avec succès",
                        "code" => 200,
                        "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at', 'desc')
                        ->where('id', $datamenage->id)->where('deleted',0)->first()
                    ], 200);
                } else {
                }
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
    public function updatemenage(Request $request, $id)
    {
        $request->validate([
            "adresse_actuel" => 'required',
            "taille" => 'required',
            'habitation' => 'required',
            'origine' => 'required',
            "datacritere" => "required|array",
            'orgid' => 'required',
        ]);
        $user = Auth::user();
        $permission = Permission::where('name', 'update_menage')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        $datamenage = MenageModel::where('id', $id)->first();
        if ($organisation) {
            if ($permission_gap) {
                if ($datamenage) {

                    $datamenage->adresse_actuel = $request->adresse_actuel;
                    $datamenage->taille = $request->taille;
                    $datamenage->habitation = $request->habitation;
                    $datamenage->origine = $request->origine;
                    $datamenage->userid = $request->userid;
                    $datamenage->save();
                    foreach ($request->datacritere as $item) {
                        $datacriteres = CritereMenageModel::where('menageid', $id)->first();
                        $datacriteres->cretereid = $item;
                        $datacriteres->save();
                    }

                    return response()->json([
                        "message" => "Traitement reussi avec succès",
                        "code" => 200,
                        "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $datamenage->id)->first()
                    ], 200);
                } else {
                }
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

    public function listcritere()
    {
        return response()->json([
            "message" => "Liste des critères de vulnerable",
            "code" => 200,
            "data" => CritereVulModel::all()
        ], 200);
    }

    public function listtypepersonne()
    {
        return response()->json([
            "message" => "Liste type personne dans un menage",
            "code" => 200,
            "data" => TypePersonneModel::all()
        ], 200);
    }

    public function listerolemenage()
    {
        return response()->json([
            "message" => "Liste de roles dans un menage",
            "code" => 200,
            "data" => RoleMenageModel::all()
        ], 200);
    }
    public function listequestion()
    {
        return response()->json([
            "message" => "Liste des questions (Si la personne est une femme)",
            "code" => 200,
            "data" => QuestionEnceinteModel::all()
        ], 200);
    }

    public function updatepersonne(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required',
            'postnom' => 'required',
            'prenom' => 'required',
            'sexe' => 'required',
            'roleid' => 'required',
            'typepersonneid' => 'required',
            'nom_pere' => 'required',
            'lieu_naissance' => 'required',
            'datenaiss' => 'required',
            "menageid" => 'required',
            "nom_mere" => 'required',
            "orgid" => 'required',
        ]);

        $user = Auth::user();
        $image = UtilController::uploadImageUrl($request->photo, '/uploads/vulnerable/');
        $permission = Permission::where('name', 'update_personne')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();

        $datamenage = MenageModel::where('id', $request->menageid)->first();
        $datapersonne = PersonnesModel::where('id', $id)->where('manageid', $request->menageid)->first();
        if ($organisation) {
            if ($permission_gap) {
                if ($datamenage) {
                    if ($datapersonne) {
                        if ($image) {
                            $datapersonne->nom = $request->nom;
                            $datapersonne->postnom = $request->postnom;
                            $datapersonne->prenom = $request->prenom;
                            $datapersonne->sexe = $request->sexe;
                            $datapersonne->roleid = $request->roleid;
                            $datapersonne->typepersonneid = $request->typepersonneid;
                            $datapersonne->nom_pere = $request->nom_pere;
                            $datapersonne->probleme_sante = $request->probleme_sante;
                            $datapersonne->lieu_naissance = $request->lieu_naissance;
                            $datapersonne->datenaiss = $request->datenaiss;
                            $datapersonne->sous_moustiquaire = $request->sous_moustiquaire;
                            $datapersonne->photo = $image;
                            $datapersonne->nom_mere = $request->nom_mere;
                            $datapersonne->manageid = $request->menageid;
                            $datapersonne->femme_enceinte = $request->femme_enceint;
                            $datapersonne->femme_allaitante = $request->femme_allaitante;
                            $datapersonne->save();

                            if (now()->diffInDays($request->datenaiss, true) < 1825) {
                                $reponse = CalendrierVaccinModel::where('personneid', $id);
                                $reponse->name = $request->calendrier;
                                $reponse->save();
                            }

                            return response()->json([
                                "message" => "La modification réussie avec succès",
                                "data" => MenageModel::with('datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $request->menageid)->get()
                            ], 200);
                        } else {
                            $datapersonne->nom = $request->nom;
                            $datapersonne->postnom = $request->postnom;
                            $datapersonne->prenom = $request->prenom;
                            $datapersonne->sexe = $request->sexe;
                            $datapersonne->roleid = $request->roleid;
                            $datapersonne->typepersonneid = $request->typepersonneid;
                            $datapersonne->nom_pere = $request->nom_pere;
                            $datapersonne->probleme_sante = $request->probleme_sante;
                            $datapersonne->lieu_naissance = $request->lieu_naissance;
                            $datapersonne->datenaiss = $request->datenaiss;
                            $datapersonne->sous_moustiquaire = $request->sous_moustiquaire;
                            $datapersonne->nom_mere = $request->nom_mere;
                            $datapersonne->manageid = $request->menageid;
                            $datapersonne->femme_enceinte = $request->femme_enceint;
                            $datapersonne->femme_allaitante = $request->femme_allaitante;
                            $datapersonne->save();


                            if (now()->diffInDays($request->datenaiss, true) < 1825) {
                                $reponse = CalendrierVaccinModel::where('personneid', $id);
                                $reponse->name = $request->calendrier;
                                $reponse->save();
                            }

                            return response()->json([
                                "message" => "La modification réussie avec succès",
                                "data" => MenageModel::with('datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $request->menageid)->get()
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            "message" => "Erreur de la modification avec cette id :" . $id,
                        ], 422);
                    }
                } else {
                    return response()->json([
                        "message" => "cette personne (" .  $datapersonne->nom = $request->nom . " " .
                            $datapersonne->postnom = $request->postnom . " ) n'existe pas",
                        "code" => 402
                    ], 402);
                }
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

    public function create_personne(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'postnom' => 'required',
            'prenom' => 'required',
            'sexe' => 'required',
            'roleid' => 'required',
            'typepersonneid' => 'required',
            'nom_pere' => 'required',
            'lieu_naissance' => 'required',
            'datenaiss' => 'required',
            'photo' => 'required',
            "menageid" => 'required',
            "nom_mere" => 'required',
            "orgid" => 'required',
        ]);

        $image = UtilController::uploadImageUrl($request->photo, '/uploads/vulnerable/');
        $user = Auth::user();
        $permission = Permission::where('name', 'create_personne')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {

                $datarole = RoleMenageModel::where('id', $request->roleid)->first();
                $datamenage_role = PersonnesModel::where('roleid', $request->roleid)
                    ->where('manageid', $request->menageid)->first();

                $datatype = TypePersonneModel::where('id', $request->typepersonneid)->first();
                $datamenage = PersonnesModel::where('typepersonneid', $request->typepersonneid)
                    ->where('manageid', $request->menageid)->first();

                if (
                    $datarole->name == "grand mére"
                    || $datarole->name == "grand père"
                    || $datarole->name == "grand frère"
                    || $datarole->name == "grand soeur"
                    || $datarole->name == "petite soeur"
                    || $datarole->name == "petit frère"
                    || $datarole->name == "Neveux"
                    || $datarole->name == "Nièce"
                ) {
                    if ($datatype->name == "Non") {

                        $datapersonne = PersonnesModel::create([
                            'nom' => $request->nom,
                            'postnom' => $request->postnom,
                            'prenom' => $request->prenom,
                            'sexe' => $request->sexe,
                            'roleid' => $request->roleid,
                            'typepersonneid' => $request->typepersonneid,
                            'nom_pere' => $request->nom_pere,
                            'probleme_sante' => $request->probleme_sante,
                            'lieu_naissance' => $request->lieu_naissance,
                            'datenaiss' => $request->datenaiss,
                            'sous_moustiquaire' => $request->sous_moustiquaire,
                            'photo' =>  $image,
                            'nom_mere' => $request->nom_mere,
                            'manageid' => $request->menageid,
                            'femme_enceinte' => $request->femme_enceinte,
                            'femme_allaitante' => $request->femme_allaitante,
                        ]);


                        if (now()->diffInDays($request->datenaiss, true) < 1825) {
                            CalendrierVaccinModel::create([
                                'name' => $request->calendrier,
                                'personneid' => $datapersonne->id,
                            ]);
                        }
                        return response()->json([
                            "message" => "Traitement reussi avec succès",
                            "code" => 200,
                            "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $request->menageid)->get()
                        ], 200);
                    } else {
                        if ($datamenage) {
                            return response()->json([
                                "message" => "Le" . " " . $datatype->name . " " . "existe déjà dans ce menage!",
                                "code" => 402,
                                "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')
                                    ->where('id', $request->menageid)->get()
                            ], 402);
                        } else {
                            $datapersonne = PersonnesModel::create([
                                'nom' => $request->nom,
                                'postnom' => $request->postnom,
                                'prenom' => $request->prenom,
                                'sexe' => $request->sexe,
                                'roleid' => $request->roleid,
                                'typepersonneid' => $request->typepersonneid,
                                'nom_pere' => $request->nom_pere,
                                'probleme_sante' => $request->probleme_sante,
                                'lieu_naissance' => $request->lieu_naissance,
                                'datenaiss' => $request->datenaiss,
                                'sous_moustiquaire' => $request->sous_moustiquaire,
                                'photo' => $image,
                                'nom_mere' => $request->nom_mere,
                                'manageid' => $request->menageid,
                                'femme_enceinte' => $request->femme_enceinte,
                                'femme_allaitante' => $request->femme_allaitante,
                            ]);


                            if (now()->diffInDays($request->datenaiss, true) < 1825) {
                                CalendrierVaccinModel::create([
                                    'name' => $request->calendrier,
                                    'personneid' => $datapersonne->id,
                                ]);
                            }
                            return response()->json([
                                "message" => "Traitement reussi avec succès",
                                "code" => 200,
                                "data" => MenageModel::with('datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $request->menageid)->get()
                            ], 200);
                        }
                    }
                } else {
                    if ($datamenage_role) {
                        return response()->json([
                            "message" => "Le" . " " . $datarole->name . " " . "existe déjà dans ce menage!",
                            "code" => 402,
                            "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')
                                ->where('id', $request->menageid)->get()
                        ], 402);
                    } else {
                        if ($datatype->name == "Non") {

                            $datapersonne = PersonnesModel::create([
                                'nom' => $request->nom,
                                'postnom' => $request->postnom,
                                'prenom' => $request->prenom,
                                'sexe' => $request->sexe,
                                'roleid' => $request->roleid,
                                'typepersonneid' => $request->typepersonneid,
                                'nom_pere' => $request->nom_pere,
                                'probleme_sante' => $request->probleme_sante,
                                'lieu_naissance' => $request->lieu_naissance,
                                'datenaiss' => $request->datenaiss,
                                'sous_moustiquaire' => $request->sous_moustiquaire,
                                'photo' => $image,
                                'nom_mere' => $request->nom_mere,
                                'manageid' => $request->menageid,
                                'femme_enceinte' => $request->femme_enceinte,
                                'femme_allaitante' => $request->femme_allaitante,
                            ]);


                            if (now()->diffInDays($request->datenaiss, true) < 1825) {
                                CalendrierVaccinModel::create([
                                    'name' => $request->calendrier,
                                    'personneid' => $datapersonne->id,
                                ]);
                            }
                            return response()->json([
                                "message" => "Traitement reussi avec succès",
                                "code" => 200,
                                "data" => MenageModel::with('datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $request->menageid)->get()
                            ], 200);
                        } else {
                            if ($datamenage) {
                                return response()->json([
                                    "message" => "Le" . " " . $datatype->name . " " . "existe déjà dans ce menage!",
                                    "code" => 402,
                                    "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')
                                        ->where('id', $request->menageid)->get()
                                ], 402);
                            } else {
                                $datapersonne = PersonnesModel::create([
                                    'nom' => $request->nom,
                                    'postnom' => $request->postnom,
                                    'prenom' => $request->prenom,
                                    'sexe' => $request->sexe,
                                    'roleid' => $request->roleid,
                                    'typepersonneid' => $request->typepersonneid,
                                    'nom_pere' => $request->nom_pere,
                                    'probleme_sante' => $request->probleme_sante,
                                    'lieu_naissance' => $request->lieu_naissance,
                                    'datenaiss' => $request->datenaiss,
                                    'sous_moustiquaire' => $request->sous_moustiquaire,
                                    'photo' => $image,
                                    'nom_mere' => $request->nom_mere,
                                    'manageid' => $request->menageid,
                                    'femme_enceinte' => $request->femme_enceinte,
                                    'femme_allaitante' => $request->femme_allaitante,
                                ]);


                                if (now()->diffInDays($request->datenaiss, true) < 1825) {
                                    CalendrierVaccinModel::create([
                                        'name' => $request->calendrier,
                                        'personneid' => $datapersonne->id,
                                    ]);
                                }
                                return response()->json([
                                    "message" => "Traitement reussi avec succès",
                                    "code" => 200,
                                    "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')->orderBy('created_at','desc')->where('id', $request->menageid)->get()
                                ], 200);
                            }
                        }
                    }
                }
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

    public function listmenage()
    {
        
        return response()->json([
            "message" => "Liste des menages",
            "code" => 200,
            "data" => MenageModel::with('dataallcritere.datacritere', 'datapersonne.datatype_personne', 'datapersonne.datarole')->where('status',0)->orderBy('created_at', 'DESC')->get()
        ], 200);
    }

    public function CodeMenage($codemenage)
    {

        $datamenage = MenageModel::where('code_menage', $codemenage)->first();
        if ($datamenage) {
            return response()->json([
                "message" => "Liste des menages",
                "code" => 200,
                "data" => MenageModel::with(
                    'dataallcritere.datacritere',
                    'datapersonne.datatype_personne',
                    'datapersonne.datarole'
                )->where('code_menage', $codemenage)->first()
            ], 200);
        } else {
            return response()->json([
                "message" => "Not data",
                "code" => 402,
            ], 402);
        }
    }

    public function DetailMenage($id)
    {
        $datamenage = MenageModel::where('id', $id)->first();
        if ($datamenage) {
            return response()->json([
                "message" => "Liste des menages",
                "code" => 200,
                "data" => MenageModel::with(
                    'dataallcritere.datacritere',
                    'datapersonne.datatype_personne',
                    'datapersonne.datarole'
                )->where('id', $id)->first()
            ], 200);
        } else {
            return response()->json([
                "message" => "Not data",
                "code" => 402,
            ], 402);
        }
    }
}
