<?php

namespace App\Http\Controllers;

use App\Models\AffectationModel;
use App\Models\AffectationPermission;
use App\Models\airesante;
use App\Models\Bloc2Model;
use App\Models\Bloc3Model;
use App\Models\Crise_Gap;
use App\Models\GapsModel;
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

                                            // INSERTION DE CAS DE MALADIES
                                            foreach ($request->datamaladie as $item) {
                                                MaladiedGap::create([
                                                    'gapid' => $bloc1->id,
                                                    'maladieid' => $item['maladieid'],
                                                    'nbrCas' =>  $item['nbrCas'],
                                                    'nbrDeces' =>  $item['nbrDeces'],
                                                ]);
                                            }
                                            // INSERTION MEDICAMENT EN RUPTURE
                                            foreach ($request->datamedocid as $item) {
                                                MedicamentRupture::create([
                                                    'gapid' => $bloc1->id,
                                                    'medocid' => $item,
                                                ]);
                                            }
                                            foreach ($request->datapartenaireid as $item) {
                                                PartenairePresntModel::create([
                                                    'gapid' => $bloc1->id,
                                                    'orgid' => $item['orgid'],
                                                    'contact_point_facal' => $item['email'],
                                                    'date_debut' => $item['date_debut'],
                                                    'date_fin' => $item['date_fin'],
                                                ]);
                                            }

                                            foreach ($request->datapartenaireid as $item) {
                                                foreach ($item["datatindicateur"] as $items) {
                                                    org_indicateur::create([
                                                        'gapid' => $bloc1->id,
                                                        'orgid' => $item['orgid'],
                                                        'indicateurid' => $items,
                                                    ]);
                                                }
                                            }

                                            foreach ($request->datatypepersonnel as $item) {
                                                PersonnelGap::create([
                                                    'gapid' => $bloc1->id,
                                                    'personnelid' => $item['typepersonnelid'],
                                                    'nbr' => $item['nbr'],
                                                ]);
                                            }

                                            foreach ($request->datacriseid as $item) {
                                                Crise_Gap::create([
                                                    'gapid' => $bloc1->id,
                                                    'criseid' => $item,
                                                ]);
                                            }

                                            foreach ($request->datapopulationeloigne as $item) {
                                                PopulationEloigne::create([
                                                    'gapid' => $bloc1->id,
                                                    'localite' => $item['localite'],
                                                    'nbr' => $item['nbr'],
                                                ]);
                                            }

                                            return response()->json([
                                                "message" => 'Traitement réussi avec succès!',
                                                "code" => 200,
                                                "data" => GapsModel::with(
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
                                                    'datascorecard.dataquestion.datarubrique'
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

                                                // INSERTION DE CAS DE MALADIES
                                                foreach ($request->datamaladie as $item) {
                                                    MaladiedGap::create([
                                                        'gapid' => $bloc1->id,
                                                        'maladieid' => $item['maladieid'],
                                                        'nbrCas' =>  $item['nbrCas'],
                                                        'nbrDeces' =>  $item['nbrDeces'],
                                                    ]);
                                                }
                                                // INSERTION MEDICAMENT EN RUPTURE
                                                foreach ($request->datamedocid as $item) {
                                                    MedicamentRupture::create([
                                                        'gapid' => $bloc1->id,
                                                        'medocid' => $item,
                                                    ]);
                                                }
                                                foreach ($request->datapartenaireid as $item) {
                                                    PartenairePresntModel::create([
                                                        'gapid' => $bloc1->id,
                                                        'orgid' => $item['orgid'],
                                                        'contact_point_facal' => $item['email'],
                                                        'date_debut' => $item['date_debut'],
                                                        'date_fin' => $item['date_fin'],
                                                    ]);
                                                }

                                                foreach ($request->datapartenaireid as $item) {
                                                    foreach ($item["datatindicateur"] as $items) {
                                                        org_indicateur::create([
                                                            'gapid' => $bloc1->id,
                                                            'orgid' => $item['orgid'],
                                                            'indicateurid' => $items,
                                                        ]);
                                                    }
                                                }

                                                foreach ($request->datatypepersonnel as $item) {
                                                    PersonnelGap::create([
                                                        'gapid' => $bloc1->id,
                                                        'personnelid' => $item['typepersonnelid'],
                                                        'nbr' => $item['nbr'],
                                                    ]);
                                                }

                                                foreach ($request->datacriseid as $item) {
                                                    Crise_Gap::create([
                                                        'gapid' => $bloc1->id,
                                                        'criseid' => $item,
                                                    ]);
                                                }

                                                foreach ($request->datapopulationeloigne as $item) {
                                                    PopulationEloigne::create([
                                                        'gapid' => $bloc1->id,
                                                        'localite' => $item['localite'],
                                                        'nbr' => $item['nbr'],
                                                    ]);
                                                }

                                                return response()->json([
                                                    "message" => 'Traitement réussi avec succès!',
                                                    "code" => 200,
                                                    "data" => GapsModel::with(
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
                                                        'datascorecard.dataquestion.datarubrique'
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
    public function update(Request $request, $id)
    {
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
                                                foreach ($request->datamaladie as $item) {
                                                    MaladiedGap::create([
                                                        'gapid' => $bloc1->id,
                                                        'maladieid' => $item['maladieid'],
                                                        'nbrCas' =>  $item['nbrCas'],
                                                        'nbrDeces' =>  $item['nbrDeces'],
                                                    ]);
                                                }
                                                // INSERTION MEDICAMENT EN RUPTURE
                                                foreach ($request->datamedocid as $item) {
                                                    MedicamentRupture::create([
                                                        'gapid' => $bloc1->id,
                                                        'medocid' => $item,
                                                    ]);
                                                }
                                                foreach ($request->datapartenaireid as $item) {
                                                    PartenairePresntModel::create([
                                                        'gapid' => $bloc1->id,
                                                        'orgid' => $item['orgid'],
                                                        'contact_point_facal' => $item['email'],
                                                        'date_debut' => $item['date_debut'],
                                                        'date_fin' => $item['date_fin'],
                                                    ]);
                                                }

                                                foreach ($request->datapartenaireid as $item) {
                                                    foreach ($item["datatindicateur"] as $items) {
                                                        org_indicateur::create([
                                                            'orgid' => $item['orgid'],
                                                            'indicateurid' => $items,
                                                        ]);
                                                    }
                                                }

                                                foreach ($request->datatypepersonnel as $item) {
                                                    PersonnelGap::create([
                                                        'gapid' => $bloc1->id,
                                                        'personnelid' => $item['typepersonnelid'],
                                                        'nbr' => $item['nbr'],
                                                    ]);
                                                }

                                                foreach ($request->datacriseid as $item) {
                                                    Crise_Gap::create([
                                                        'gapid' => $bloc1->id,
                                                        'criseid' => $item,
                                                    ]);
                                                }

                                                foreach ($request->datapopulationeloigne as $item) {
                                                    PopulationEloigne::create([
                                                        'gapid' => $bloc1->id,
                                                        'localite' => $item['localite'],
                                                        'nbr' => $item['nbr'],
                                                    ]);
                                                }

                                                return response()->json([
                                                    "message" => 'Traitement réussi avec succès!',
                                                    "code" => 200,
                                                    "data" => GapsModel::with(
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
                                                        'datascorecard.dataquestion.datarubrique'
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
                        'datascorecard.dataquestion.datarubrique'
                    )->orderBy('created_at', 'desc')->get(),
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
                'datascorecard.dataquestion.datarubrique'
            )->get(),
            "code" => 200,
        ], 200);
    }
    public function listGapByuser($orgid)
    {

        $user = Auth::user();
        if (GapsModel::where('userid', $user->id)->where('orguserid', $orgid)->where('status', 0)->exists()) {
            return response()->json([
                "message" => 'Liste des gaps validés',
                "code" => 200,
                "data" => GapsModel::with(
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
                    'datascorecard'
                )->orderBy('created_at', 'desc')->where('userid', $user->id)->where('orguserid', $orgid)->where('status', 0)->get()
            ]);
        } else {
            return response()->json([
                "message" => "Not data"
            ], 402);
        }
    }
    public function listGapValideByuser($orgid)
    {
        $user = Auth::user();
        if (GapsModel::where('userid', $user->id)->where('orguserid', $orgid)->where('status', 1)->exists()) {
            return response()->json([
                "message" => 'Liste des gaps validés',
                "code" => 200,
                "data" => GapsModel::with(
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
                    'datascorecard'
                )->orderBy('created_at', 'desc')->where('userid', $user->id)->where('orguserid', $orgid)->where('status', 1)->get()
            ]);
        } else {
            return response()->json([
                "message" => "Not data"
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
                    'datascorecard'
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
                    'datascorecard.dataquestion.datarubrique'
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
                    'datascorecard.dataquestion.datarubrique'
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
                    'datascorecard.dataquestion.datarubrique'
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
                    'datascorecard.dataquestion.datarubrique'
                )->where('airid', $aire->id)->get(),
            ]);
        } else {
            return response()->json([
                "message" => "zoneid not found "
            ], 402);
        }
    }
}
