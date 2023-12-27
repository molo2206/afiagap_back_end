<?php

namespace App\Http\Controllers;

use App\Models\AffectationModel;
use App\Models\AffectationPermission;
use App\Models\airesante;
use App\Models\Bloc2Model;
use App\Models\Bloc3Model;
use App\Models\Crise_Gap;
use App\Models\GapsModel;
use App\Models\ImageGapModel;
use App\Models\MaladiedGap;
use App\Models\MedicamentRupture;
use App\Models\org_indicateur;
use App\Models\Organisation;
use App\Models\PartenairePresntModel;
use App\Models\Permission;
use App\Models\PersonnelGap;
use App\Models\PersonnelModel;
use App\Models\PopulationEloigne;
use App\Models\province;
use App\Models\territoir;
use App\Models\TypeCrise;
use App\Models\zonesante;
use App\Models\structureSanteModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GapsController extends Controller
{
    public function AddGap(Request $request)
    {

        $request->validate([
            // Bloc1
            'provinceid' => 'required',
            'territoirid' => 'required',
            'zoneid' => 'required',
            'airid' => 'required',
            'structureid' => 'required',
            'dateadd' => 'required',
            'orgid' => 'required',
        ]);
        $datagap = GapsModel::where('orgid', $request->structureid)->orderBy('dateadd', 'desc')->first();
        if ($datagap == null) {
            $province = province::find($request->provinceid);
            $territoir = territoir::find($request->territoirid);
            $zone = zonesante::find($request->zoneid);
            $aire = airesante::find($request->airid);
            $structure = structureSanteModel::find($request->structureid);

            $date = date('d/m/y');
            $timestamp = date('H:i:s');

            $user = Auth::user();

            $permission = Permission::where('name', 'create_gap')->first();
            $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
            if ($organisation) {
                if ($permission) {
                    $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
                    $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
                        ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
                    if ($permission_gap) {
                        if ($province) {
                            if ($territoir) {
                                if ($zone) {
                                    if ($aire) {
                                        if ($structure) {
                                            $bloc1 = GapsModel::create([
                                                'title' => $structure->name . ' ' . $date . ' ' . $timestamp,
                                                'provinceid' => $request->provinceid,
                                                'territoirid' => $request->territoirid,
                                                'zoneid' => $request->zoneid,
                                                'airid' => $request->airid,
                                                'orgid' => $request->structureid,
                                                'population' => $request->population,
                                                'pop_deplace' => $request->pop_deplace,
                                                'pop_retourne' => $request->pop_retourne,
                                                'pop_site' => $request->pop_site,
                                                'userid' => $user->id,
                                                'semaine_epid' => $request->semaine_epid,
                                                'annee_epid' => $request->annee_epid,
                                                "dateadd" => $request->dateadd,
                                                "orguserid" => $request->orgid
                                            ]);

                                            $bloc2 = Bloc2Model::create([
                                                'bloc1id' => $bloc1->id,
                                                'etat_infra' => $request->etat_infra,
                                                'equipement' => $request->equipement,
                                                'nbr_lit' => $request->nbr_lit,
                                                'taux_occupation' => $request->taux_occupation,
                                                'nbr_reco' => $request->nbr_reco,
                                                'pop_eloigne' => $request->pop_eloigne,
                                                'pop_vulnerable' => $request->pop_vulnerable,
                                            ]);

                                            Bloc3Model::create([
                                                'bloc2id' => $bloc2->id,
                                                'cout_ambulatoire' => $request->cout_ambulatoire,
                                                'cout_hospitalisation' => $request->cout_hospitalisation,
                                                'cout_accouchement' => $request->cout_accouchement,
                                                'cout_cesarienne' => $request->cout_cesarienne,
                                                'barriere' => $request->barriere,
                                                'pop_handicap' => $request->pop_handicap,
                                                'couvertureDtc3' => $request->couvertureDtc3,
                                                'mortaliteLessfiveyear' => $request->mortaliteLessfiveyear,
                                                'covid19_nbrcas' => $request->covid19_nbrcas,
                                                'covid19_nbrdeces' => $request->covid19_nbrdeces,
                                                'covid19_nbrtest' => $request->covid19_nbrtest,
                                                'covid19_vacciDispo' => $request->covid19_vacciDispo,
                                                'pourcentCleanWater' => $request->pourcentCleanWater,
                                                'malnutrition' => $request->malnutrition,
                                            ]);

                                            //INSERTION DE CAS DE MALADIES
                                            $gap = GapsModel::where('id', $bloc1->id)->first();
                                            if ($gap) {
                                                $gap->maladiegap()->detach();
                                                foreach ($request->datamaladie as $item) {
                                                    $gap->maladiegap()->attach([$bloc1->id =>
                                                    [
                                                        'maladieid' => $item['maladieid'],
                                                        'nbrCas' => $item['nbrCas'],
                                                        'nbrDeces' => $item['nbrDeces'],
                                                    ]]);
                                                }
                                            }

                                            // INSERTION MEDICAMENT EN RUPTURE
                                            if ($gap) {
                                                $gap->medicamentrupture()->detach();
                                                foreach ($request->datamedocid as $item) {
                                                    $gap->medicamentrupture()->attach([$bloc1->id =>
                                                    [
                                                        'medocid' => $item,
                                                    ]]);
                                                }
                                            }

                                            //INSERTION PARTENAIRE PRESENT

                                            if ($gap) {
                                                $gap->partenairegap()->detach();
                                                foreach ($request->datapartenaireid as $item) {
                                                    $gap->partenairegap()->attach([$bloc1->id =>
                                                    [
                                                        'orgid' => $item['orgid'],
                                                        'contact_point_facal' => $item['email'],
                                                        'date_debut' => $item['date_debut'],
                                                        'date_fin' => $item['date_fin'],
                                                    ]]);
                                                }
                                            }

                                            //INSERTION INDICATEURS PARTENAIRE PRESENT
                                            if ($gap) {
                                                $gap->indicateurgap()->detach();
                                                foreach ($request->datapartenaireid as $item) {
                                                    foreach ($item["datatindicateur"] as $items) {
                                                        $gap->indicateurgap()->attach([$bloc1->id =>
                                                        [
                                                            'orgid' => $item['orgid'],
                                                            'indicateurid' => $items,
                                                        ]]);
                                                    }
                                                }
                                            }


                                            //INSERTION TYPE PERSONNELS
                                            if ($gap) {
                                                $gap->typepersonnelgap()->detach();
                                                foreach ($request->datatypepersonnel as $item) {
                                                    $gap->typepersonnelgap()->attach([$bloc1->id =>
                                                    [
                                                        'personnelid' => $item['typepersonnelid'],
                                                        'nbr' => $item['nbr'],
                                                    ]]);
                                                }
                                            }

                                            //INSERTION CRISE GAP
                                            if ($gap) {
                                                $gap->crisegap()->detach();
                                                foreach ($request->datacriseid as $item) {
                                                    $gap->crisegap()->attach([$bloc1->id =>
                                                    [
                                                        'criseid' => $item,
                                                    ]]);
                                                }
                                            }

                                            //INSERTION POPULATION ELOIGNE GAP
                                            if ($gap) {
                                                $gap->populationeloignegap()->detach();
                                                foreach ($request->datapopulationeloigne as $item) {
                                                    $gap->populationeloignegap()->attach([$bloc1->id =>
                                                    [
                                                        'localite' => $item['localite'],
                                                        'nbr' => $item['nbr'],
                                                    ]]);
                                                }
                                            }
                                             //INSERTION IMAGES GAP
                                            if ($bloc1) {
                                                
                                                if ($request->images) {
                                                    foreach ($request->images as $item) {
                                                        $image = UtilController::uploadMultipleImage($item, '/uploads/gap/');
                                                        $bloc1->imagesgap()->attach([$bloc1->id =>
                                                        [
                                                            'image' => $image,
                                                        ]]);
                                                    }
                                                }
                                            }

                                            return response()->json([
                                                "message" => 'Traitement réussi avec succès!',
                                                "code" => 200,
                                                "data" => GapsModel::with(
                                                    'datauser',
                                                    'suite1.suite2',
                                                    'dataprovince',
                                                    'dataterritoir',
                                                    'datazone',
                                                    'dataaire',
                                                    'datastructure',
                                                    'datapopulationEloigne',
                                                    'datamaladie.maladie',
                                                    'allcrise.crise',
                                                    'datamedicament.medicament',
                                                    'datapartenaire.partenaire.allindicateur.paquetappui',
                                                    'datatypepersonnel.typepersonnel',
                                                    'datascorecard.dataquestion.datarubrique',
                                                    'images'
                                                )->where('id', $bloc1->id)->orderBy('created_at', 'desc')->where('userid', $user->id)->where('orguserid', $request->orgid)->where('status', 0)->first(),
                                            ], 200);
                                        } else {
                                            return response()->json([
                                                "message" => "structureid not found "
                                            ], 402);
                                        }
                                    } else {
                                        return response()->json([
                                            "message" => "aireid not found "
                                        ], 402);
                                    }
                                } else {
                                    return response()->json([
                                        "message" => "zoneid not found "
                                    ], 402);
                                }
                            } else {
                                return response()->json([
                                    "message" => "territoirid not found "
                                ], 402);
                            }
                        } else {
                            return response()->json([
                                "message" => "provinceid not found "
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
                        "message" => "cette permission" . $permission->name . "n'existe pas",
                        "code" => 402
                    ], 402);
                }
            } else {
                return response()->json([
                    "message" => "cette organisationid" . $organisation->id . "n'existe pas",
                    "code" => 402
                ], 402);
            }
        } else {
            $fdate = $request->dateadd;
            $tdate = $datagap->dateadd;
            $datetime1 = new DateTime($fdate);
            $datetime2 = new DateTime($tdate);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');

            if ($days > 30) {
                $province = province::find($request->provinceid);
                $territoir = territoir::find($request->territoirid);
                $zone = zonesante::find($request->zoneid);
                $aire = airesante::find($request->airid);
                $structure = structureSanteModel::find($request->structureid);

                $date = date('d/m/y');
                $timestamp = date('H:i:s');

                $user = Auth::user();

                $permission = Permission::where('name', 'create_gap')->first();
                $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
                if ($organisation) {
                    if ($permission) {
                        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
                        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
                            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
                        if ($permission_gap) {
                            if ($province) {
                                if ($territoir) {
                                    if ($zone) {
                                        if ($aire) {
                                            if ($structure) {
                                                $bloc1 = GapsModel::create([
                                                    'title' => $structure->name . ' ' . $date . ' ' . $timestamp,
                                                    'provinceid' => $request->provinceid,
                                                    'territoirid' => $request->territoirid,
                                                    'zoneid' => $request->zoneid,
                                                    'airid' => $request->airid,
                                                    'orgid' => $request->structureid,
                                                    'population' => $request->population,
                                                    'pop_deplace' => $request->pop_deplace,
                                                    'pop_retourne' => $request->pop_retourne,
                                                    'pop_site' => $request->pop_site,
                                                    'userid' => $user->id,
                                                    'semaine_epid' => $request->semaine_epid,
                                                    'annee_epid' => $request->annee_epid,
                                                    "dateadd" => $request->dateadd,
                                                    "orguserid" => $request->orgid
                                                ]);

                                                $bloc2 = Bloc2Model::create([
                                                    'bloc1id' => $bloc1->id,
                                                    'etat_infra' => $request->etat_infra,
                                                    'equipement' => $request->equipement,
                                                    'nbr_lit' => $request->nbr_lit,
                                                    'taux_occupation' => $request->taux_occupation,
                                                    'nbr_reco' => $request->nbr_reco,
                                                    'pop_eloigne' => $request->pop_eloigne,
                                                    'pop_vulnerable' => $request->pop_vulnerable,
                                                ]);

                                                Bloc3Model::create([
                                                    'bloc2id' => $bloc2->id,
                                                    'cout_ambulatoire' => $request->cout_ambulatoire,
                                                    'cout_hospitalisation' => $request->cout_hospitalisation,
                                                    'cout_accouchement' => $request->cout_accouchement,
                                                    'cout_cesarienne' => $request->cout_cesarienne,
                                                    'barriere' => $request->barriere,
                                                    'pop_handicap' => $request->pop_handicap,
                                                    'couvertureDtc3' => $request->couvertureDtc3,
                                                    'mortaliteLessfiveyear' => $request->mortaliteLessfiveyear,
                                                    'covid19_nbrcas' => $request->covid19_nbrcas,
                                                    'covid19_nbrdeces' => $request->covid19_nbrdeces,
                                                    'covid19_nbrtest' => $request->covid19_nbrtest,
                                                    'covid19_vacciDispo' => $request->covid19_vacciDispo,
                                                    'pourcentCleanWater' => $request->pourcentCleanWater,
                                                    'malnutrition' => $request->malnutrition,
                                                ]);
                                                
                                                $gap = GapsModel::where('id', $bloc1->id)->first();
                                                 //INSERTION CRISE GAP
                                                if ($gap) {
                                                    $gap->crisegap()->detach();
                                                    foreach ($request->datacriseid as $item) {
                                                        $gap->crisegap()->attach([$bloc1->id =>
                                                        [
                                                            'criseid' => $item,
                                                        ]]);
                                                    }
                                                }

                                                // INSERTION DE CAS DE MALADIES
                                                if ($gap) {
                                                    $gap->maladiegap()->detach();
                                                    foreach ($request->datamaladie as $item) {
                                                        $gap->maladiegap()->attach([$bloc1->id =>
                                                        [
                                                            'maladieid' => $item['maladieid'],
                                                            'nbrCas' => $item['nbrCas'],
                                                            'nbrDeces' => $item['nbrDeces'],
                                                        ]]);
                                                    }
                                                }

                                                // INSERTION MEDICAMENT EN RUPTURE
                                                if ($gap) {
                                                    $gap->medicamentrupture()->detach();
                                                    foreach ($request->datamedocid as $item) {
                                                        $gap->medicamentrupture()->attach([$bloc1->id =>
                                                        [
                                                            'medocid' => $item,
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION PARTENAIRE PRESENT

                                                if ($gap) {
                                                    $gap->partenairegap()->detach();
                                                    foreach ($request->datapartenaireid as $item) {
                                                        $gap->partenairegap()->attach([$bloc1->id =>
                                                        [
                                                            'orgid' => $item['orgid'],
                                                            'contact_point_facal' => $item['email'],
                                                            'date_debut' => $item['date_debut'],
                                                            'date_fin' => $item['date_fin'],
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION INDICATEURS PARTENAIRE PRESENT
                                                if ($gap) {
                                                    $gap->indicateurgap()->detach();
                                                    foreach ($request->datapartenaireid as $item) {
                                                        foreach ($item["datatindicateur"] as $items) {
                                                            $gap->indicateurgap()->attach([$bloc1->id =>
                                                            [
                                                                'orgid' => $item['orgid'],
                                                                'indicateurid' => $items,
                                                            ]]);
                                                        }
                                                    }
                                                }


                                                //INSERTION TYPE PERSONNELS
                                                if ($gap) {
                                                    $gap->typepersonnelgap()->detach();
                                                    foreach ($request->datatypepersonnel as $item) {
                                                        $gap->typepersonnelgap()->attach([$bloc1->id =>
                                                        [
                                                            'personnelid' => $item['typepersonnelid'],
                                                            'nbr' => $item['nbr'],
                                                        ]]);
                                                    }
                                                }

                                               

                                                //INSERTION POPULATION ELOIGNE GAP
                                                if ($gap) {
                                                    $gap->populationeloignegap()->detach();
                                                    foreach ($request->datapopulationeloigne as $item) {
                                                        $gap->populationeloignegap()->attach([$bloc1->id =>
                                                        [
                                                            'localite' => $item['localite'],
                                                            'nbr' => $item['nbr'],
                                                        ]]);
                                                    }
                                                }
                                               if ($bloc1) {
                                              
                                                if ($request->images) {
                                                    foreach ($request->images as $item) {
                                                        $image = UtilController::uploadMultipleImage($item, '/uploads/gap/');
                                                        $bloc1->imagesgap()->attach([$bloc1->id =>
                                                        [
                                                            'image' => $image,
                                                        ]]);
                                                    }
                                                }
                                            }

                                                return response()->json([
                                                    "message" => 'Traitement réussi avec succès!',
                                                    "code" => 200,
                                                    "data" => GapsModel::with(
                                                        'datauser',
                                                        'suite1.suite2',
                                                        'dataprovince',
                                                        'dataterritoir',
                                                        'datazone',
                                                        'dataaire',
                                                        'datastructure',
                                                        'datapopulationEloigne',
                                                        'datamaladie.maladie',
                                                        'allcrise.crise',
                                                        'datamedicament.medicament',
                                                        'datapartenaire.partenaire.allindicateur.paquetappui',
                                                        'datatypepersonnel.typepersonnel',
                                                        'datascorecard.dataquestion.datarubrique',
                                                        'images'
                                                    )->where('id', $bloc1->id)->orderBy('created_at', 'desc')->where('userid', $user->id)->where('orguserid', $request->orgid)->where('status', 0)->first(),
                                                ], 200);
                                            } else {
                                                return response()->json([
                                                    "message" => "structureid not found "
                                                ], 402);
                                            }
                                        } else {
                                            return response()->json([
                                                "message" => "aireid not found "
                                            ], 402);
                                        }
                                    } else {
                                        return response()->json([
                                            "message" => "zoneid not found "
                                        ], 402);
                                    }
                                } else {
                                    return response()->json([
                                        "message" => "territoirid not found "
                                    ], 402);
                                }
                            } else {
                                return response()->json([
                                    "message" => "provinceid not found "
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
                            "message" => "cette permission" . $permission->name . "n'existe pas",
                            "code" => 402
                        ], 402);
                    }
                } else {
                    return response()->json([
                        "message" => "cette organisationid" . $organisation->id . "n'existe pas",
                        "code" => 402
                    ], 402);
                }
            } else {
                return response()->json([
                    "message" => "Le dernier gap doit atteindre 30 jours pour envoyer un nouveau gap",
                    "code" => 402
                ], 402);
            }
        }
    }
    //update gap
    public function UpdateGap(Request $request, $gapid)
    {
        $request->validate([
            // Bloc1
            'provinceid' => 'required',
            'territoirid' => 'required',
            'zoneid' => 'required',
            'airid' => 'required',
            'structureid' => 'required',
            'orgid' => 'required',
        ]);
        $datagap = GapsModel::where('deleted', 0)
            ->where('id', $gapid)->where('children', null)->first();
        $province = province::find($request->provinceid);
        $territoir = territoir::find($request->territoirid);
        $zone = zonesante::find($request->zoneid);
        $aire = airesante::find($request->airid);
        $structure = structureSanteModel::find($request->structureid);
        $user = Auth::user();
        $namepermission = 'update_gap';
        $permission = Permission::where('name', $namepermission)->first();
        $organisation = Organisation::find($request->orgid);
        if ($organisation) {
            if ($permission) {
                $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
                $permission_valide_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
                    ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();

                if ($permission_valide_gap) {
                    $datavalide = GapsModel::where('id', $gapid)->whereNotNull('children')->where('deleted', 0)->first();
                    if ($datavalide) {
                        return response()->json([
                            "message" => "Ce gap est déjà validé, on ne peut le modifier pour l'instant!",
                            "code" => 422,
                        ], 422);
                    } else {
                        if ($datagap) {
                            if ($province) {
                                if ($territoir) {
                                    if ($zone) {
                                        if ($aire) {
                                            if ($structure) {
                                                $datagap->provinceid = $request->provinceid;
                                                $datagap->territoirid = $request->territoirid;
                                                $datagap->zoneid = $request->zoneid;
                                                $datagap->airid = $request->airid;
                                                $datagap->orgid = $request->structureid;
                                                $datagap->population = $request->population;
                                                $datagap->pop_deplace = $request->pop_deplace;
                                                $datagap->pop_retourne = $request->pop_retourne;
                                                $datagap->pop_site = $request->pop_site;
                                                $datagap->userid = $user->id;
                                                $datagap->semaine_epid = $request->semaine_epid;
                                                $datagap->annee_epid = $request->annee_epid;
                                                $datagap->save();

                                                $datagap_bloc2 = Bloc2Model::where('deleted', 0)
                                                    ->where('bloc1id', $datagap->id)->first();

                                                $datagap_bloc2->etat_infra = $request->etat_infra;
                                                $datagap_bloc2->equipement = $request->equipement;
                                                $datagap_bloc2->nbr_lit = $request->nbr_lit;
                                                $datagap_bloc2->taux_occupation = $request->taux_occupation;
                                                $datagap_bloc2->nbr_reco = $request->nbr_reco;
                                                $datagap_bloc2->pop_eloigne = $request->pop_eloigne;
                                                $datagap_bloc2->pop_vulnerable = $request->pop_vulnerable;
                                                $datagap_bloc2->save();

                                                $datagap_bloc3 = Bloc3Model::where('deleted', 0)
                                                    ->where('bloc2id', $datagap_bloc2->id)->first();
                                                $datagap_bloc3->cout_ambulatoire = $request->cout_ambulatoire;
                                                $datagap_bloc3->cout_hospitalisation = $request->cout_hospitalisation;
                                                $datagap_bloc3->cout_accouchement = $request->cout_accouchement;
                                                $datagap_bloc3->cout_cesarienne = $request->cout_cesarienne;
                                                $datagap_bloc3->barriere = $request->barriere;
                                                $datagap_bloc3->pop_handicap = $request->pop_handicap;
                                                $datagap_bloc3->couvertureDtc3 = $request->couvertureDtc3;
                                                $datagap_bloc3->mortaliteLessfiveyear = $request->mortaliteLessfiveyear;
                                                $datagap_bloc3->covid19_nbrcas = $request->covid19_nbrcas;
                                                $datagap_bloc3->covid19_nbrdeces = $request->covid19_nbrdeces;
                                                $datagap_bloc3->covid19_nbrtest = $request->covid19_nbrtest;
                                                $datagap_bloc3->covid19_vacciDispo = $request->covid19_vacciDispo;
                                                $datagap_bloc3->pourcentCleanWater = $request->pourcentCleanWater;
                                                $datagap_bloc3->malnutrition = $request->malnutrition;
                                                $datagap_bloc3->save();

                                                if ($datagap) {
                                                    $datagap->maladiegap()->detach();
                                                    foreach ($request->datamaladie as $item) {
                                                        $datagap->maladiegap()->attach([$datagap->id =>
                                                        [
                                                            'maladieid' => $item['maladieid'],
                                                            'nbrCas' => $item['nbrCas'],
                                                            'nbrDeces' => $item['nbrDeces'],
                                                        ]]);
                                                    }
                                                }

                                                // INSERTION MEDICAMENT EN RUPTURE
                                                if ($datagap) {
                                                    $datagap->medicamentrupture()->detach();
                                                    foreach ($request->datamedocid as $item) {
                                                        $datagap->medicamentrupture()->attach([$datagap->id =>
                                                        [
                                                            'medocid' => $item,
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION PARTENAIRE PRESENT

                                                if ($datagap) {
                                                    $datagap->partenairegap()->detach();
                                                    foreach ($request->datapartenaireid as $item) {
                                                        $datagap->partenairegap()->attach([$datagap->id =>
                                                        [
                                                            'orgid' => $item['orgid'],
                                                            'contact_point_facal' => $item['email'],
                                                            'date_debut' => $item['date_debut'],
                                                            'date_fin' => $item['date_fin'],
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION INDICATEURS PARTENAIRE PRESENT
                                                if ($datagap) {
                                                    $datagap->indicateurgap()->detach();
                                                    foreach ($request->datapartenaireid as $item) {
                                                        foreach ($item["datatindicateur"] as $items) {
                                                            $datagap->indicateurgap()->attach([$datagap->id =>
                                                            [
                                                                'orgid' => $item['orgid'],
                                                                'indicateurid' => $items,
                                                            ]]);
                                                        }
                                                    }
                                                }


                                                //INSERTION TYPE PERSONNELS
                                                if ($datagap) {
                                                    $datagap->typepersonnelgap()->detach();
                                                    foreach ($request->datatypepersonnel as $item) {
                                                        $datagap->typepersonnelgap()->attach([$datagap->id =>
                                                        [
                                                            'personnelid' => $item['typepersonnelid'],
                                                            'nbr' => $item['nbr'],
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION CRISE GAP
                                                if ($datagap) {
                                                    $datagap->crisegap()->detach();
                                                    foreach ($request->datacriseid as $item) {
                                                        $datagap->crisegap()->attach([$datagap->id =>
                                                        [
                                                            'criseid' => $item,
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION POPULATION ELOIGNE GAP
                                                if ($datagap) {
                                                    $datagap->populationeloignegap()->detach();
                                                    foreach ($request->datapopulationeloigne as $item) {
                                                        $datagap->populationeloignegap()->attach([$datagap->id =>
                                                        [
                                                            'localite' => $item['localite'],
                                                            'nbr' => $item['nbr'],
                                                        ]]);
                                                    }
                                                }

                                                //  //INSERTION IMAGES GAP
                                                // if ($datagap) {
                                                //     $datagap->imagesgap()->detach();
                                                //     if($request->images){
                                                //         foreach ($request->images as $item) {
                                                //             $image = UtilController::uploadMultipleImage($item, '/uploads/gap/');
                                                //             $datagap->imagesgap()->attach([$datagap->id =>
                                                //             [
                                                //                 'image' => $image,
                                                //             ]]);
                                                //         }
                                                //     }
                                                // }


                                                return response()->json([
                                                    "message" => 'Modification réussie avec succès!',
                                                    "code" => 200,
                                                    "data" => GapsModel::with(
                                                        'datauser',
                                                        'suite1.suite2',
                                                        'dataprovince',
                                                        'dataterritoir',
                                                        'datazone',
                                                        'dataaire',
                                                        'datastructure',
                                                        'datapopulationEloigne',
                                                        'datamaladie.maladie',
                                                        'allcrise.crise',
                                                        'datamedicament.medicament',
                                                        'datapartenaire.partenaire.allindicateur.paquetappui',
                                                        'datatypepersonnel.typepersonnel',
                                                        'datascorecard.dataquestion.datarubrique',
                                                        'images'
                                                    )->where('id', $gapid)->orderBy('created_at', 'desc')->where('userid', $user->id)->where('orguserid', $request->orgid)->where('status', 0)->first(),
                                                ], 200);
                                            } else {
                                                return response()->json([
                                                    "message" => "structureid not found "
                                                ], 402);
                                            }
                                        } else {
                                            return response()->json([
                                                "message" => "aireid not found "
                                            ], 402);
                                        }
                                    } else {
                                        return response()->json([
                                            "message" => "zoneid not found "
                                        ], 402);
                                    }
                                } else {
                                    return response()->json([
                                        "message" => "territoirid not found "
                                    ], 402);
                                }
                            } else {
                                return response()->json([
                                    "message" => "provinceid not found "
                                ], 402);
                            }
                        } else {
                            return response()->json([
                                "message" => "Ce gap est déjà validé on ne peut le modifier pour l'instant!",
                                "code" => 422,
                            ], 422);
                        }
                    }
                } else {
                    return response()->json([
                        "message" => "Vous ne pouvez pas éffectuer cette action"
                    ], 402);
                }
            } else {
                return response()->json([
                    "message" => "cette permission" . $permission->name . "n'existe pas",
                    "code" => 402
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "cette organisation" . $organisation->name . "n'existe pas",
                "code" => 402
            ], 402);
        }
    }

    //Delete gap
    public function deletegap(Request $request, $id)
    {

        $request->validate([
            "orgid" => 'required'
        ]);

        $user = Auth::user();
        $permission = Permission::where('name', 'delete_gap')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                $gap = GapsModel::where('id', $id)->where('orguserid', $request->orgid)->where('status', 0)->where('deleted', 0)->first();
                if ($gap) {
                    $gap->deleted = 1;
                    $gap->save();
                    return response()->json([
                        "message" => 'Liste des gaps',
                        "code" => 200,
                        "data" => GapsModel::with(
                            'datauser',
                            'suite1.suite2',
                            'dataprovince',
                            'dataterritoir',
                            'datazone',
                            'dataaire',
                            'datastructure',
                            'datapopulationEloigne',
                            'datamaladie.maladie',
                            'allcrise.crise',
                            'datamedicament.medicament',
                            'datapartenaire.partenaire.allindicateur.paquetappui',
                            'datatypepersonnel.typepersonnel',
                            'datascorecard.dataquestion.datarubrique',
                            'images'
                        )->orderBy('created_at', 'desc')->where('orguserid', $request->orgid)->where('status', 0)->where('deleted', 0)->where('children', null)->get()
                    ]);
                } else {
                    return response()->json([
                        "message" => 'Cette identifiant est erronné dans le système!',
                        "code" => 402,
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
    //validation du gap
    public function valideGap(Request $request, $gapid)
    {
        $request->validate([
            'provinceid' => 'required',
            'territoirid' => 'required',
            'zoneid' => 'required',
            'airid' => 'required',
            'structureid' => 'required',
        ]);

        $province = province::find($request->provinceid);
        $territoir = territoir::find($request->territoirid);
        $zone = zonesante::find($request->zoneid);
        $aire = airesante::find($request->airid);
        $structure = structureSanteModel::find($request->structureid);

        $user = Auth::user();

        $date = date('y-m-d');
        $timestamp = date('H:i:s');
        $namepermission = 'valide_gap';

        $permission = Permission::where('name', $namepermission)->first();
        $organisation = Organisation::find($request->orgid);
        if ($organisation) {
            if ($permission) {
                $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $request->orgid)->first();
                $permission_valide_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
                    ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();

                if ($permission_valide_gap) {
                    $datagap = GapsModel::where('id', $gapid)->where('status', 0)->first();
                    $datavalide = GapsModel::where('id', $gapid)->where('status', 1)->where('deleted', 0)->first();
                    if ($datavalide) {
                        return response()->json([
                            "message" => "Ce gap est déjà validé",
                            "code" => 422,
                        ], 422);
                    } else {
                        if ($datagap) {
                            $datagap->status = 1;
                            $datagap->save();

                            if ($province) {
                                if ($territoir) {
                                    if ($zone) {
                                        if ($aire) {
                                            if ($structure) {
                                                $bloc1 = GapsModel::create([
                                                    'title' => $structure->name . ' ' . $date . ' ' . $timestamp,
                                                    'provinceid' => $request->provinceid,
                                                    'territoirid' => $request->territoirid,
                                                    'zoneid' => $request->zoneid,
                                                    'airid' => $request->airid,
                                                    'orgid' => $request->structureid,
                                                    'population' => $request->population,
                                                    'pop_deplace' => $request->pop_deplace,
                                                    'pop_retourne' => $request->pop_retourne,
                                                    'pop_site' => $request->pop_site,
                                                    'userid' => $user->id,
                                                    'children' => $gapid,
                                                    'status' => 1,
                                                    'semaine_epid' => $request->semaine_epid,
                                                    'annee_epid' => $request->annee_epid,
                                                    'dateadd' => $request->dateadd
                                                ]);

                                                $bloc2 = Bloc2Model::create([
                                                    'bloc1id' => $bloc1->id,
                                                    'etat_infra' => $request->etat_infra,
                                                    'equipement' => $request->equipement,
                                                    'nbr_lit' => $request->nbr_lit,
                                                    'taux_occupation' => $request->taux_occupation,
                                                    'nbr_reco' => $request->nbr_reco,
                                                    'pop_eloigne' => $request->pop_eloigne,
                                                    'pop_vulnerable' => $request->pop_vulnerable,
                                                ]);

                                                Bloc3Model::create([
                                                    'bloc2id' => $bloc2->id,
                                                    'cout_ambulatoire' => $request->cout_ambulatoire,
                                                    'cout_hospitalisation' => $request->cout_hospitalisation,
                                                    'cout_accouchement' => $request->cout_accouchement,
                                                    'cout_cesarienne' => $request->cout_cesarienne,
                                                    'barriere' => $request->barriere,
                                                    'pop_handicap' => $request->pop_handicap,
                                                    'couvertureDtc3' => $request->couvertureDtc3,
                                                    'mortaliteLessfiveyear' => $request->mortaliteLessfiveyear,
                                                    'covid19_nbrcas' => $request->covid19_nbrcas,
                                                    'covid19_nbrdeces' => $request->covid19_nbrdeces,
                                                    'covid19_nbrtest' => $request->covid19_nbrtest,
                                                    'covid19_vacciDispo' => $request->covid19_vacciDispo,
                                                    'pourcentCleanWater' => $request->pourcentCleanWater,
                                                    'malnutrition' => $request->malnutrition,
                                                ]);

                                                // INSERTION DE CAS DE MALADIES
                                                $gap = GapsModel::where('id', $bloc1->id)->first();
                                                if ($gap) {
                                                    $gap->maladiegap()->detach();
                                                    foreach ($request->datamaladie as $item) {
                                                        $gap->maladiegap()->attach([$bloc1->id =>
                                                        [
                                                            'maladieid' => $item['maladieid'],
                                                            'nbrCas' => $item['nbrCas'],
                                                            'nbrDeces' => $item['nbrDeces'],
                                                        ]]);
                                                    }
                                                }

                                                // INSERTION MEDICAMENT EN RUPTURE
                                                if ($gap) {
                                                    $gap->medicamentrupture()->detach();
                                                    foreach ($request->datamedocid as $item) {
                                                        $gap->medicamentrupture()->attach([$bloc1->id =>
                                                        [
                                                            'medocid' => $item,
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION PARTENAIRE PRESENT

                                                if ($gap) {
                                                    $gap->partenairegap()->detach();
                                                    foreach ($request->datapartenaireid as $item) {
                                                        $gap->partenairegap()->attach([$bloc1->id =>
                                                        [
                                                            'orgid' => $item['orgid'],
                                                            'contact_point_facal' => $item['email'],
                                                            'date_debut' => $item['date_debut'],
                                                            'date_fin' => $item['date_fin'],
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION INDICATEURS PARTENAIRE PRESENT
                                                if ($gap) {
                                                    $gap->indicateurgap()->detach();
                                                    foreach ($request->datapartenaireid as $item) {
                                                        foreach ($item["datatindicateur"] as $items) {
                                                            $gap->indicateurgap()->attach([$bloc1->id =>
                                                            [
                                                                'orgid' => $item['orgid'],
                                                                'indicateurid' => $items,
                                                            ]]);
                                                        }
                                                    }
                                                }


                                                //INSERTION TYPE PERSONNELS
                                                if ($gap) {
                                                    $gap->typepersonnelgap()->detach();
                                                    foreach ($request->datatypepersonnel as $item) {
                                                        $gap->typepersonnelgap()->attach([$bloc1->id =>
                                                        [
                                                            'personnelid' => $item['typepersonnelid'],
                                                            'nbr' => $item['nbr'],
                                                        ]]);
                                                    }
                                                }

                                                //INSERTION TYPE PERSONNELS
                                                if ($gap) {
                                                    $gap->crisegap()->detach();
                                                    foreach ($request->datacriseid as $item) {
                                                        $gap->crisegap()->attach([$bloc1->id =>
                                                        [
                                                            'criseid' => $item,
                                                        ]]);
                                                    }
                                                }


                                                if ($gap) {
                                                    $gap->populationeloignegap()->detach();
                                                    foreach ($request->datapopulationeloigne as $item) {
                                                        $gap->populationeloignegap()->attach([$bloc1->id =>
                                                        [
                                                            'localite' => $item['localite'],
                                                            'nbr' => $item['nbr'],
                                                        ]]);
                                                    }
                                                }

                                                 //INSERTION IMAGES GAP
                                            if ($bloc1) {
                                                $bloc1->imagesgap()->detach();
                                                if ($request->images) {
                                                    foreach ($request->images as $item) {
                                                        $image = UtilController::uploadMultipleImage($item, '/uploads/gap/');
                                                        $bloc1->imagesgap()->attach([$datagap->id =>
                                                        [
                                                            'image' => $image,
                                                        ]]);
                                                    }
                                                }
                                            }

                                                return response()->json([
                                                    "message" => 'Traitement réussi avec succès!',
                                                    "code" => 200,
                                                    "data" => GapsModel::with(
                                                        'datauser',
                                                        'suite1.suite2',
                                                        'dataprovince',
                                                        'dataterritoir',
                                                        'datazone',
                                                        'dataaire',
                                                        'datastructure',
                                                        'datapopulationEloigne',
                                                        'datamaladie.maladie',
                                                        'allcrise.crise',
                                                        'datamedicament.medicament',
                                                        'datapartenaire.partenaire.allindicateur.paquetappui',
                                                        'datatypepersonnel.typepersonnel',
                                                        'datascorecard.dataquestion.datarubrique',
                                                        'images'
                                                    )->where('userid', $user->id)->where('orguserid', $request->orgid)->where('status', 1)->get(),
                                                ], 200);
                                            } else {
                                                return response()->json([
                                                    "message" => "structureid not found "
                                                ], 402);
                                            }
                                        } else {
                                            return response()->json([
                                                "message" => "aireid not found "
                                            ], 402);
                                        }
                                    } else {
                                        return response()->json([
                                            "message" => "zoneid not found "
                                        ], 402);
                                    }
                                } else {
                                    return response()->json([
                                        "message" => "territoirid not found "
                                    ], 402);
                                }
                            } else {
                                return response()->json([
                                    "message" => "provinceid not found "
                                ], 402);
                            }
                        } else {
                            return response()->json([
                                "message" => "Ce gap est déjà validé",
                                "code" => 422,
                            ], 422);
                        }
                    }
                } else {
                    return response()->json([
                        "message" => "Vous ne pouvez pas éffectuer cette action"
                    ], 402);
                }
            } else {
                return response()->json([
                    "message" => "cette permission" . $permission->name . "n'existe pas",
                    "code" => 402
                ], 402);
            }
        } else {
            return response()->json([
                "message" => "cette organisation" . $organisation->name . "n'existe pas",
                "code" => 402
            ], 402);
        }
    }

    public function listGap($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_gap')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                return response()->json([
                    "message" => 'Liste des gaps!',
                    "code" => 200,
                    "data" => GapsModel::with(
                        'datauser',
                        'suite1.suite2',
                        'dataprovince',
                        'dataterritoir',
                        'datazone',
                        'dataaire',
                        'datastructure',
                        'datapopulationEloigne',
                        'datamaladie.maladie',
                        'allcrise.crise',
                        'datamedicament.medicament',
                        'datapartenaire.partenaire.allindicateur.paquetappui',
                        'datatypepersonnel.typepersonnel',
                        'datascorecard.dataquestion.datarubrique',
                        'images'
                    )->orderBy('created_at', 'desc')->where('deleted', 0)->where('status', 0)->where('children', null)->get(),
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
    public function listgap1()
    {
        return response()->json([
            "data" => GapsModel::with(
                'datauser',
                'suite1.suite2',
                'dataprovince',
                'dataterritoir',
                'datazone',
                'dataaire',
                'datastructure',
                'datapopulationEloigne',
                'datamaladie.maladie',
                'allcrise.crise',
                'datamedicament.medicament',
                'datapartenaire.partenaire.allindicateur.paquetappui',
                'datatypepersonnel.typepersonnel',
                'datascorecard.dataquestion.datarubrique',
                'images'
            )->get(),
            "code" => 200,
        ], 200);
    }
    public function listGapByuser($orgid)
    {
        $user = Auth::user();
        if (GapsModel::where('userid', $user->id)->where('orguserid', $orgid)->where('status', 0)->exists()) {
            return response()->json([
                "message" => 'Liste des gaps!',
                "code" => 200,
                "data" => GapsModel::with(
                    'datauser',
                    'suite1.suite2',
                    'dataprovince',
                    'dataterritoir',
                    'datazone',
                    'dataaire',
                    'datastructure',
                    'datapopulationEloigne',
                    'datamaladie.maladie',
                    'allcrise.crise',
                    'datamedicament.medicament',
                    'datapartenaire.partenaire.allindicateur.paquetappui',
                    'datatypepersonnel.typepersonnel',
                    'datascorecard.dataquestion.datarubrique',
                    'images'
                )->orderBy('created_at', 'desc')->where('userid', $user->id)->where('orguserid', $orgid)->where('status', 0)->get()
            ]);
        } else {
            return response()->json([
                "message" => "Not data"
            ], 402);
        }
    }
    public function listGapValide($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_gap_valide')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                if (GapsModel::where('status', 1)->whereNot('children', null)->exists()) {
                    return response()->json([
                        "message" => 'Liste des gaps validés',
                        "code" => 200,
                        "data" => GapsModel::with(
                            'datauser',
                            'suite1.suite2',
                            'dataprovince',
                            'dataterritoir',
                            'datazone',
                            'dataaire',
                            'datastructure',
                            'datapopulationEloigne',
                            'datamaladie.maladie',
                            'allcrise.crise',
                            'datamedicament.medicament',
                            'datapartenaire.partenaire.allindicateur.paquetappui',
                            'datatypepersonnel.typepersonnel',
                            'datascorecard.dataquestion.datarubrique',
                        )->orderBy('created_at', 'desc')->where('status', 1)->whereNot('children', null)->get()
                    ]);
                } else {
                    return response()->json([
                        "message" => "Not data"
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
    public function listGapValideByuser($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_gap_valide')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                if (GapsModel::where('userid', $user->id)->where('orguserid', $orgid)->where('deleted', 0)->where('status', 1)->exists()) {
                    return response()->json([
                        "message" => 'Liste des gaps validés',
                        "code" => 200,
                        "data" => GapsModel::with(
                            'datauser',
                            'suite1.suite2',
                            'dataprovince',
                            'dataterritoir',
                            'datazone',
                            'dataaire',
                            'datastructure',
                            'datapopulationEloigne',
                            'datamaladie.maladie',
                            'allcrise.crise',
                            'datamedicament.medicament',
                            'datapartenaire.partenaire.allindicateur.paquetappui',
                            'datatypepersonnel.typepersonnel',
                            'datascorecard.dataquestion.datarubrique',
                            'images'
                        )->orderBy('created_at', 'desc')->where('userid', $user->id)->where('orguserid', $orgid)->where('deleted', 0)->where('status', 1)->get()
                    ]);
                } else {
                    return response()->json([
                        "message" => "Not data"
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
    public function listGapValideRepondu($orgid)
    {
        $user = Auth::user();
        $permission = Permission::where('name', 'view_gap_repondu')->first();
        $organisation = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $affectationuser = AffectationModel::where('userid', $user->id)->where('orgid', $orgid)->first();
        $permission_gap = AffectationPermission::with('permission')->where('permissionid', $permission->id)
            ->where('affectationid', $affectationuser->id)->where('deleted', 0)->where('status', 0)->first();
        if ($organisation) {
            if ($permission_gap) {
                if (GapsModel::where('status', 2)->whereNot('children', null)->exists()) {
                    return response()->json([
                        "message" => 'Liste des gaps validés',
                        "code" => 200,
                        "data" => GapsModel::with(
                            'datauser',
                            'suite1.suite2',
                            'dataprovince',
                            'dataterritoir',
                            'datazone',
                            'dataaire',
                            'datastructure',
                            'datapopulationEloigne',
                            'datamaladie.maladie',
                            'allcrise.crise',
                            'datamedicament.medicament',
                            'datapartenaire.partenaire.allindicateur.paquetappui',
                            'datatypepersonnel.typepersonnel',
                            'datascorecard.dataquestion.datarubrique',
                            'images'
                        )->orderBy('created_at', 'desc')->where('status', 2)->whereNot('children', null)->get()
                    ]);
                } else {
                    return response()->json([
                        "message" => "Not data"
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
    public function DetailGaps($id)
    {
        $gap = GapsModel::find($id);
        if ($gap) {
            return response()->json([
                "message" => 'Detail Gap!',
                "code" => 200,
                "data" => GapsModel::with(
                    'datauser',
                    'suite1.suite2',
                    'dataprovince',
                    'dataterritoir',
                    'datazone',
                    'dataaire',
                    'datastructure',
                    'datapopulationEloigne',
                    'datamaladie.maladie',
                    'allcrise.crise',
                    'datamedicament.medicament',
                    'datapartenaire.partenaire.allindicateur.paquetappui',
                    'datatypepersonnel.typepersonnel',
                    'datascorecard.dataquestion.datarubrique',
                    'images'
                )->where('id', $id)->first(),
            ]);
        } else {
            return response()->json([
                "message" => "id not found "
            ], 402);
        }
    }

    public function listGapProvince($provinceid)
    {
        $province = province::find($provinceid);
        if ($province) {
            return response()->json([
                "message" => 'Liste des gaps!',
                "code" => 200,
                "data" => GapsModel::with(
                    'datauser',
                    'suite1.suite2',
                    'dataprovince',
                    'dataterritoir',
                    'datazone',
                    'dataaire',
                    'datastructure',
                    'datapopulationEloigne',
                    'datamaladie.maladie',
                    'allcrise.crise',
                    'datamedicament.medicament',
                    'datapartenaire.partenaire.allindicateur.paquetappui',
                    'datatypepersonnel.typepersonnel',
                    'datascorecard.dataquestion.datarubrique',
                    'images'
                )->where('provinceid', $province->id)->get(),
            ]);
        } else {
            return response()->json([
                "message" => "provinceid not found "
            ], 402);
        }
    }
    public function listGapTerritoir($territoirid)
    {
        $territoir = territoir::find($territoirid);
        if ($territoir) {
            return response()->json([
                "message" => 'Liste des gaps!',
                "code" => 200,
                "data" => GapsModel::with(
                    'datauser',
                    'suite1.suite2',
                    'dataprovince',
                    'dataterritoir',
                    'datazone',
                    'dataaire',
                    'datastructure',
                    'datapopulationEloigne',
                    'datamaladie.maladie',
                    'allcrise.crise',
                    'datamedicament.medicament',
                    'datapartenaire.partenaire.allindicateur.paquetappui',
                    'datatypepersonnel.typepersonnel',
                    'datascorecard.dataquestion.datarubrique',
                )->where('territoirid', $territoir->id)->get(),
            ]);
        } else {
            return response()->json([
                "message" => "territoirid not found "
            ], 402);
        }
    }
    public function listGapZone($zoneid)
    {
        $zone = territoir::find($zoneid);
        if ($zone) {
            return response()->json([
                "message" => 'Liste des gaps!',
                "code" => 200,
                "data" => GapsModel::with(
                    'datauser',
                    'suite1.suite2',
                    'dataprovince',
                    'dataterritoir',
                    'datazone',
                    'dataaire',
                    'datastructure',
                    'datapopulationEloigne',
                    'datamaladie.maladie',
                    'allcrise.crise',
                    'datamedicament.medicament',
                    'datapartenaire.partenaire.allindicateur.paquetappui',
                    'datatypepersonnel.typepersonnel',
                    'datascorecard.dataquestion.datarubrique',
                    'images'
                )->where('zoneid', $zone->id)->get(),
            ]);
        } else {
            return response()->json([
                "message" => "zoneid not found "
            ], 402);
        }
    }
    public function listGapAire($airid)
    {
        $aire = territoir::find($airid);
        if ($aire) {
            return response()->json([
                "message" => 'Liste des gaps!',
                "code" => 200,
                "data" => GapsModel::with(
                    'datauser',
                    'suite1.suite2',
                    'dataprovince',
                    'dataterritoir',
                    'datazone',
                    'dataaire',
                    'datastructure',
                    'datapopulationEloigne',
                    'datamaladie.maladie',
                    'allcrise.crise',
                    'datamedicament.medicament',
                    'datapartenaire.partenaire.allindicateur.paquetappui',
                    'datatypepersonnel.typepersonnel',
                    'datascorecard.dataquestion.datarubrique',
                    'images'
                )->where('airid', $aire->id)->get(),
            ]);
        } else {
            return response()->json([
                "message" => "zoneid not found "
            ], 402);
        }
    }

    public function getlastgapvalide()
    {

        $dt = new DateTime();
        $startDate = $dt->format('Y-m-d');

        return response()->json([
            "message" => 'Derniers gap validés par structure',
            "code" => 200,
            "data" => GapsModel::with(
                'datauser',
                'suite1.suite2',
                'dataprovince',
                'dataterritoir',
                'datazone',
                'dataaire',
                'datastructure',
                'datapopulationEloigne',
                'datamaladie.maladie',
                'allcrise.crise',
                'datamedicament.medicament',
                'datapartenaire.partenaire.allindicateur.paquetappui',
                'datatypepersonnel.typepersonnel',
                'datascorecard.dataquestion.datarubrique',
                'images'
            )->orderby('dateadd', 'desc')->whereNot('children', null)->get()
        ]);
    }
    
     public function Imagegap(Request $request,$id)
    {

        $datagap = GapsModel::where('id', $id)->first();
        if ($datagap) {
            // $datagap->imagesgap()->detach();
            $image=UtilController::uploadMultipleImage($request->images, '/uploads/gap/');
            foreach ($image as $item) {
                $datagap->imagesgap()->attach([$datagap->id =>
                [
                    'image' => $item,
                ]]);
            }
            return response()->json([
                "message" => 'Traitement réussi avec succès!',
                "code" => 200,
                "data" => GapsModel::with(
                    'datauser',
                    'suite1.suite2',
                    'dataprovince',
                    'dataterritoir',
                    'datazone',
                    'dataaire',
                    'datastructure',
                    'datapopulationEloigne',
                    'datamaladie.maladie',
                    'allcrise.crise',
                    'datamedicament.medicament',
                    'datapartenaire.partenaire.allindicateur.paquetappui',
                    'datatypepersonnel.typepersonnel',
                    'datascorecard.dataquestion.datarubrique',
                    'images'
                )->where('id', $id)->first(),
            ], 200);
        } else {
            return response()->json([
                "message" => "C'est identifiant de gap n'existe pas!",
                "code" => 402,
            ], 402);
        }
    }
}
