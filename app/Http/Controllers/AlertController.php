<?php

namespace App\Http\Controllers;

use App\Models\AffectationModel;
use App\Models\AffectationPermission;
use App\Models\airesante;
use App\Models\AlertModel;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function sendAlert(Request $request)
    {
        $request->validate([
            'name_point_focal' => 'required',
            "phone" => 'required',
            "airid" => 'required',
            "date_notification" => 'required',
            "datealert" => 'required',
            "timealert" => 'required',
            "nbr_touche" => 'required',
            "dece_disponible" => 'required',
            "nbr_dece" => 'required',
            "animal_malade" => 'required',
            "animal_mort" => 'required',
            "evenement" => 'required',
            "mesure" => 'required',
            "maladieid" => 'required',
            "nb_animal_malade" => 'required',
            "nb_animal_mort" => 'required',
            "date_detection" => 'required',
            "time_detection"  => 'required',
            "orgid" => 'required',
        ]);
        $user = Auth::user();
        $permission = Permission::where('name', 'create_alert')->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_send_alert = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($permission_send_alert) {
            $aire = airesante::find($request->airid);
            if ($aire) {
                if ($request->dece_disponible == "oui" || $request->dece_disponible == "non") {
                    if ($request->animal_malade == "oui" || $request->animal_malade == "non") {
                        if ($request->animal_mort == "oui" || $request->animal_mort == "non") {
                            if ($request->evenement == "oui" || $request->evenement == "non") {
                                $alert = AlertModel::create([
                                    'name_point_focal' => $request->name_point_focal,
                                    "phone" => $request->phone,
                                    "airid" => $request->airid,
                                    "date_notification" => $request->date_notification,
                                    "datealert" => $request->datealert,
                                    "timealert" => $request->timealert,
                                    "nbr_touche" => $request->nbr_touche,
                                    "dece_disponible" => $request->dece_disponible,
                                    "nbr_dece" => $request->nbr_dece,
                                    "animal_malade" => $request->animal_malade,
                                    "animal_mort" => $request->animal_mort,
                                    "evenement" => $request->evenement,
                                    "mesure" => $request->mesure,
                                    "maladieid" => $request->maladieid,
                                    "description" => $request->description,
                                    "nb_animal_malade" => $request->nb_animal_malade,
                                    "nb_animal_mort" => $request->nb_animal_mort,
                                    "date_detection" => $request->date_detection,
                                    "time_detection"  => $request->time_detection,
                                    "userid" => $user->id,
                                    "orguserid" => $affectationuser->orgid
                                ]);
                                return response()->json([
                                    "code" => 200,
                                    "message" => 'Alert envoyé avec succès!',
                                    "data" => AlertModel::with(
                                        'dataaire.zonesante.territoir.province',
                                        'maladie'
                                    )->where('deleted', 0)->where('status', 0)->get(),
                                ], 200);
                            } else {
                                return response()->json([
                                    "message" => "evemenent doit etre soit oui ou non !"
                                ], 402);
                            }
                        } else {
                            return response()->json([
                                "message" => "animal_mort doit etre soit oui ou non !"
                            ], 402);
                        }
                    } else {
                        return response()->json([
                            "message" => "animal_malade doit etre soit oui ou non !"
                        ], 402);
                    }
                } else {
                    return response()->json([
                        "message" => "dece_disponible doit etre soit oui ou non !"
                    ], 402);
                }
            } else {
                return response()->json([
                    "message" => "aireid not found "
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "Vous ne pouvez pas éffectuer cette action"
            ], 402);
        }
    }

    public function validerAlert(Request $request, $alertid)
    {

        $request->validate([
            'name_point_focal' => 'required',
            "phone" => 'required',
            "airid" => 'required',
            "date_notification" => 'required',
            "datealert" => 'required',
            "timealert" => 'required',
            "nbr_touche" => 'required',
            "dece_disponible" => 'required',
            "nbr_dece" => 'required',
            "animal_malade" => 'required',
            "animal_mort" => 'required',
            "evenement" => 'required',
            "mesure" => 'required',
            "maladieid" => 'required',
            "nb_animal_malade" => 'required',
            "nb_animal_mort" => 'required',
            "date_detection" => 'required',
            "time_detection"  => 'required',
            "orgid" => 'required',
        ]);

        $user = Auth::user();

        $permission = Permission::where('name', 'investigation_alert')->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_send_alert = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();

        if ($permission_send_alert) {
            $datagalert = AlertModel::where('id', $alertid)->where('status', 0)->first();
            $dataalertvalide = AlertModel::where('id', $alertid)->where('status', 1)->where('deleted', 0)->first();
            if ($dataalertvalide) {
                return response()->json([
                    "message" => "Cette alerte est déjà validé",
                    "code" => 422,
                ], 422);
            } else {
                if ($datagalert) {
                    $datagalert->status = 1;
                    $datagalert->save();
                }
                $aire = airesante::find($request->airid);
                if ($aire) {
                    if ($request->dece_disponible == "oui" || $request->dece_disponible == "non") {
                        if ($request->animal_malade == "oui" || $request->animal_malade == "non") {
                            if ($request->animal_mort == "oui" || $request->animal_mort == "non") {
                                if ($request->evenement == "oui" || $request->evenement == "non") {
                                    $alert = AlertModel::create([
                                        'name_point_focal' => $request->name_point_focal,
                                        "phone" => $request->phone,
                                        "airid" => $request->airid,
                                        "date_notification" => $request->date_notification,
                                        "datealert" => $request->datealert,
                                        "timealert" => $request->timealert,
                                        "nbr_touche" => $request->nbr_touche,
                                        "dece_disponible" => $request->dece_disponible,
                                        "nbr_dece" => $request->nbr_dece,
                                        "animal_malade" => $request->animal_malade,
                                        "animal_mort" => $request->animal_mort,
                                        "evenement" => $request->evenement,
                                        "mesure" => $request->mesure,
                                        "maladieid" => $request->maladieid,
                                        "description" => $request->description,
                                        "nb_animal_malade" => $request->nb_animal_malade,
                                        "nb_animal_mort" => $request->nb_animal_mort,
                                        "date_detection" => $request->date_detection,
                                        "time_detection"  => $request->time_detection,
                                        "userid" => $user->id,
                                        "children" => $datagalert->id,
                                        "orguserid" => $request->orgid
                                    ]);
                                    return response()->json([
                                        "message" => 'Alert investiguée avec succès!',
                                        "data" => AlertModel::with(
                                            'dataaire.zonesante.territoir.province',
                                            'maladie'
                                        )->where('deleted', 0)->where('status', 0)->get(),
                                    ], 200);
                                } else {
                                    return response()->json([
                                        "message" => "evemenent doit etre soit oui ou non !"
                                    ], 402);
                                }
                            } else {
                                return response()->json([
                                    "message" => "animal_mort doit etre soit oui ou non !"
                                ], 402);
                            }
                        } else {
                            return response()->json([
                                "message" => "animal_malade doit etre soit oui ou non !"
                            ], 402);
                        }
                    } else {
                        return response()->json([
                            "message" => "dece_disponible doit etre soit oui ou non !"
                        ], 402);
                    }
                } else {
                    return response()->json([
                        "message" => "aireid not found "
                    ], 402);
                }
            }
        } else {
            return response()->json([
                "message" => "Vous ne pouvez pas éffectuer cette action"
            ], 402);
        }
    }


    public function updateAlert(Request $request, $id)
    {
        $request->validate([
            'name_point_focal' => 'required',
            "phone" => 'required',
            "airid" => 'required',
            "date_notification" => 'required',
            "datealert" => 'required',
            "timealert" => 'required',
            "nbr_touche" => 'required',
            "dece_disponible" => 'required',
            "nbr_dece" => 'required',
            "animal_malade" => 'required',
            "animal_mort" => 'required',
            "evenement" => 'required',
            "mesure" => 'required',
            "maladieid" => 'required',
            "nb_animal_malade" => 'required',
            "nb_animal_mort" => 'required',
            "date_detection" => 'required',
            "time_detection"  => 'required',
            "orgid" => 'required',
        ]);

        $user = Auth::user();
        $permission = Permission::where('name', 'create_alert')->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_send_alert = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($permission_send_alert) {
            $datagalert = AlertModel::where('id', $id)->where('status', '0')->first();
            if ($datagalert) {
                $aire = airesante::find($request->airid);
                if ($aire) {
                    if ($request->dece_disponible == "oui" || $request->dece_disponible == "non") {
                        if ($request->animal_malade == "oui" || $request->animal_malade == "non") {
                            if ($request->animal_mort == "oui" || $request->animal_mort == "non") {
                                if ($request->evenement == "oui" || $request->evenement == "non") {
                                    $datagalert->name_point_focal = $request->name_point_focal;
                                    $datagalert->phone = $request->phone;
                                    $datagalert->airid = $request->airid;
                                    $datagalert->date_notification = $request->date_notification;
                                    $datagalert->datealert = $request->datealert;
                                    $datagalert->timealert = $request->timealert;
                                    $datagalert->nbr_touche = $request->nbr_touche;
                                    $datagalert->dece_disponible = $request->dece_disponible;
                                    $datagalert->nbr_dece = $request->nbr_dece;
                                    $datagalert->animal_malade = $request->animal_malade;
                                    $datagalert->animal_mort = $request->animal_mort;
                                    $datagalert->evenement = $request->evenement;
                                    $datagalert->mesure = $request->mesure;
                                    $datagalert->maladieid = $request->maladieid;
                                    $datagalert->description = $request->description;
                                    $datagalert->nb_animal_malade = $request->nb_animal_malade;
                                    $datagalert->nb_animal_mort = $request->nb_animal_mort;
                                    $datagalert->date_detection = $request->date_detection;
                                    $datagalert->time_detection = $request->time_detection;
                                    $datagalert->userid = $user->id;
                                    $datagalert->children = $datagalert->id;
                                    $datagalert->orguserid = $request->orgid;
                                    $datagalert->save();
                                    return response()->json([
                                        "code" => 200,
                                        "message" => 'Alert modifié avec succès!',
                                        "data" => AlertModel::with(
                                            'dataaire.zonesante.territoir.province',
                                            'maladie'
                                        )->where('deleted', 0)->where('status', 0)->get(),
                                    ], 200);
                                } else {
                                    return response()->json([
                                        "message" => "evemenent doit etre soit oui ou non !"
                                    ], 402);
                                }
                            } else {
                                return response()->json([
                                    "message" => "animal_mort doit etre soit oui ou non !"
                                ], 402);
                            }
                        } else {
                            return response()->json([
                                "message" => "animal_malade doit etre soit oui ou non !"
                            ], 402);
                        }
                    } else {
                        return response()->json([
                            "message" => "dece_disponible doit etre soit oui ou non !"
                        ], 402);
                    }
                } else {
                    return response()->json([
                        "message" => "aireid not found "
                    ], 402);
                }
            } else {
                return response()->json([
                    "message" => "Erreur de traitement avec cette id:" . $id
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "Vous ne pouvez pas éffectuer cette action"
            ], 402);
        }
    }

    public function suppressionalert(Request $request, $id)
    {
        $request->validate([
            "orgid" => "required"
        ]);

        $user = Auth::user();
        $permission = Permission::where('name', 'delete_alert')->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_send_alert = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($permission_send_alert) {
            $datagalert = AlertModel::where('id', $id)->where('deleted', 0)->where('status', 0)->first();
            if ($datagalert) {
                $datagalert->deleted = 1;
                $datagalert->save();
                return response()->json([
                    "code" => 200,
                    "message" => 'Alerte est rejeté avec succès!',
                    "data" => AlertModel::with(
                        'dataaire.zonesante.territoir.province',
                        'maladie'
                    )->where('deleted', 0)->where('status', 0)->get(),
                ], 200);
            } else {
                return response()->json([
                    "code" => 402,
                    "message" => 'Cette alerte n\'existe pas dans le système',
                ], 200);
            }
        } else {
            return response()->json([
                "code" => 402,
                "message" => "Vous ne pouvez pas éffectuer cette action"
            ], 402);
        }
    }

    public function getAlert($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_alert')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                return response()->json([
                    "message" => "Liste des alerts",
                    "data" => AlertModel::with('dataaire.zonesante.territoir.province', 'maladie')->where('status', 0)->where('deleted', 0)->get(),
                ], 200);
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
    public function getAlertvalide($orgid)
    {

        $user = Auth::user();
        $permission = Permission::where('name', 'view_alert_valide')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                return response()->json([
                    "message" => "Liste des alertes validées de ".$user-> full_name."(".$user->email.")",
                    "data" => AlertModel::with('dataaire.zonesante.territoir.province', 'maladie')->whereNotNull('children')->where('deleted', 0)->where('status', 0)->get(),
                ], 200);
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

    public function getDetailAlert($id)
    {
        $alert = AlertModel::find($id);
        if ($alert) {
            return response()->json([
                "message" => "Detail de l'alert",
                "data" => AlertModel::with('dataaire.zonesante.territoir.province', 'maladie')->where('id', $alert->id)->where('deleted', 0)->where('status', 0)->first(),
            ], 200);
        } else {
            return response()->json([
                "message" => "Identifiant not found",
                "code" => "402"
            ], 402);
        }
    }
    public function alertuser($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_alert_valide')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                if ($user) {
                    return response()->json([
                        "message" => "Liste des alerts de ".$user-> full_name."(".$user->email.")",
                        "data" => AlertModel::with('dataaire.zonesante.territoir.province', 'maladie')->where('deleted', 0)->where('status', 0)->where('userid', $user->id)->get(),
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Identifiant not found",
                        "code" => "402"
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
}
