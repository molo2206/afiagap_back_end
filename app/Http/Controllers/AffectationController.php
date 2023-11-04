<?php

namespace App\Http\Controllers;

use App\Models\AffectationModel;
use App\Models\AffectationPermission;
use App\Models\Organisation;
use App\Models\Permission;
use App\Models\RoleModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffectationController extends Controller
{
    public function Affectation(Request $request)
    {
        $request->validate([
            'userid' => 'required',
            'orgid' => 'required'
        ]);
        $user = Auth::user();
        $permission = Permission::where('name', 'affectation_org')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                $affectation = AffectationModel::where('userid', $request->userid)->where('orgid', $request->orgid)->first();
                if ($affectation) {
                    if ($request->orgid == null) {
                        $affectation->orgid = $affectation->orgid;
                    } else {
                        $affectation->orgid = $request->orgid;
                    }
                    if ($request->roleid == null) {
                        $affectation->roleid = $affectation->roleid;
                    } else {
                        $affectation->roleid = $request->roleid;
                    }
                    $affectation->orgid = $request->orgid;
                    $affectation->roleid = $request->roleid;
                    $affectation->userid = $request->userid;
                    $affectation->save();
                    return response()->json([
                        "message" => "Affctation réussie avec succèss",
                        "data" => AffectationModel::with('user', 'organisation', 'role')->where('userid', $request->userid)->where('orgid', $request->orgid)->first()
                    ], 200);
                } else {
                    if (Organisation::where('id', $request->orgid)->first()) {

                        if (RoleModel::where('id', $request->roleid)->first()) {

                            if (User::where('id', $request->userid)->first()) {
                                $aff = AffectationModel::create([
                                    'orgid' => $request->orgid,
                                    'roleid' => $request->roleid,
                                    'userid' => $request->userid,
                                ]);
                                return response()->json([
                                    "message" => "Affctation réussie avec succès",
                                    "data" => AffectationModel::with('user', 'organisation', 'role')->where('userid', $request->userid)->where('orgid', $request->orgid)->first()
                                ], 200);
                            } else {
                                return response()->json([
                                    "message" => "C'est utilisateur n'existe pas dans le système",
                                ], 422);
                            }
                        } else {
                            return response()->json([
                                "message" => "C'est role n'existe pas dans le système "
                            ], 422);
                        }
                    } else {
                        return response()->json([
                            "message" => "Cette organisation n'existe pas dans le système ",
                        ], 422);
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

    public function create_permission(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'psedo' => 'required',
            'orgid' => 'required',
        ]);

        $user = Auth::user();
        $permission = Permission::where('name', 'create_permission')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                if (Permission::where('name', $request->name)->exists()) {
                    return response()->json([
                        "message" => "Cette permission existe déjà dans le système ",
                    ], 422);
                } else {
                    Permission::create([
                        'name' => $request->name,
                        'psedo' => $request->psedo,
                    ]);
                    return response()->json([
                        "message" => "Création de la permission réussie avec succès"
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

    public function update_permission(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'psedo' => 'required',
            'orgid' => 'required',
        ]);
        $user = Auth::user();
        $permission = Permission::where('name', 'update_permission')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                $permission = Permission::find($id);
                if ($permission) {
                    $permission->name = $request->name;
                    $permission->psedo = $request->psedo;
                    $permission->save();
                    return response()->json([
                        "message" => "La modification de la permission réussie"
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Erreur de la modification permission",
                    ], 422);
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

    public function list_permissions($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_permission')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                return response()->json([
                    "message" => "Liste des permissions",
                    "data" => Permission::all(),
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

    public function RetirerAcces(Request $request)
    {
        $request->validate([
            'idaffect_perm' => 'required',
            'orgid' => 'required'
        ]);

        $user = Auth::user();
        $permission = Permission::where('name', 'retire_acces')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                foreach ($request->idaffect_perm as $item) {
                    $affectationpermission = AffectationPermission::find($item);
                    if ($affectationpermission) {
                        $affectationpermission->deleted = 1;
                        $affectationpermission->delete();
                    }
                }
                return response()->json([
                    "message" => "Permission rétirée avec succès",
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

    public function affecterPermission(Request $request)
    {
        $request->validate([
            'affectationid' => 'required',
            'permissionid' => 'required',
            'orgid' => 'required',
        ]);
        $user = Auth::user();
        $permission = Permission::where('name', 'create_permission')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                $affectation = AffectationModel::where('id', $request->affectationid)->first();
                if ($affectation) {
                    foreach ($request->permissionid as $item) {
                        AffectationPermission::create([
                            'affectationid' => $request->affectationid,
                            'permissionid' => $item,
                        ]);
                    }
                    return response()->json([
                        "message" => "Permission accordée",

                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Vous devez d'abord etre affecter",
                    ], 422);
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

    public function List_PermissionsAccordees($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_permission')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                return response()->json([
                    "code" => "200",
                    "message" => "Liste des permissions",
                    "data" => AffectationPermission::with('permission')->get(),
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
}
