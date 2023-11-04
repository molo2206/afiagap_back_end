<?php

namespace App\Http\Controllers;

use App\Models\EnteteScoreModel;
use App\Models\QuestionModel;
use App\Models\ReponseModel;
use App\Models\GapsModel;
use Illuminate\Http\Request;

class ScoreCardController extends Controller
{
    public function AddEntete(Request $request)
    {
        $request->validate([
            "name_entete" => "required",
        ]);
        $entetescore = EnteteScoreModel::where('name_entete', $request->name_entete)->exists();
        if ($entetescore) {
            return response()->json([
                "message" => "Cette nomaclature :(" . $request->name_entete . ") dans le système!",
            ], 422);
        } else {
            EnteteScoreModel::create([
                "name_entete" => $request->name_entete
            ]);
            return response()->json([
                "message" => "Traitement réussie!"
            ], 200);
        }
    }
    public function list_entete()
    {
         return response()->json([
            "message" => "Liste des entetes ScoreCard",
            "code" => 200,
            "data" => EnteteScoreModel::with('dataquestion')->get(),
        ], 200);
    }
    public function addquestion(Request $request)
    {
        $request->validate([
            "name_question" => "required",
            'enteteid' => "required",
        ]);
        $question = QuestionModel::where('name_question', $request->name_question)->exists();
        if ($question) {
            return response()->json([
                "message" => "Cette question " . "(" . $request->name_question . ")" . " existe dans le système!",
            ], 422);
        } else {
            $entetescore = EnteteScoreModel::find($request->enteteid);
            if ($entetescore) {
                QuestionModel::create([
                    "name_question" => $request->name_question,
                    "enteteid" => $request->enteteid
                ]);
            } else {
                return response()->json([
                    "message" => "Traitement réussie!",
                    "code" => "200"
                ], 200);
            }
            return response()->json([
                "message" => "Traitement réussie!",
                "code" => "200"
            ], 200);
        }
    }
    public function ListQuestionRubrique($id)
    {
        $entete = EnteteScoreModel::find($id);
        if ($entete) {
            return response()->json([
                "message" => "Liste des questionnaires",
                "data" => EnteteScoreModel::with('dataquestion')->where('id', $id)->first(),
                "code" => 200,
            ], 200);
        } else {
            return response()->json([
                "message" => "Identifiant not found",
                "code" => "422"
            ], 422);
        }
    }
    public function sendscoreCard(Request $request)
    {
        $request->validate([
            'gapid' => 'required',
            "datareponse" => "required|array",
        ]);
        $gapid = GapsModel::find($request->gapid);
        $datascore = ReponseModel::where('gapid',$request->gapid)->first();
        if ($datascore)
        {
            foreach ($request->datareponse as $item)
            {
             $reponse_question = ReponseModel::where('questionid',$item['questionid'])->first();
             $reponse_question->response=$item['reponse'];
             $reponse_question->save();
            }
            return response()->json([
                "message" => "Traitement réussie avec succès!",
                "code" => "200",
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
                )->where('id',$gapid->id)->first(),
            ], 200);

        }else{
            if ($gapid) {
                foreach ($request->datareponse as $item) {
                    ReponseModel::create([
                        'gapid' => $request->gapid,
                        'response' => $item['reponse'],
                        'questionid' => $item['questionid'],
                    ]);
                }
                return response()->json([
                    "message" => "Traitement réussie avec succès!",
                    "code" => "200",
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
                    )->where('id',$gapid->id)->first(),
                ], 200);

            } else {
                return response()->json([
                    "message" => "Identifiant gap :" . ($request->gapid) . " not foud!",
                    "code" => "422"
                ], 422);
            }
        }

    }
}
