<?php

use App\Http\Controllers\AdressController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\CriseController;
use App\Http\Controllers\GapsController;
use App\Http\Controllers\MaladieController;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\Pyramide;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\AffectationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\MenageController;
use App\Http\Controllers\ScoreCardController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Authentification
Route::post('/create_account', [UserController::class, 'Register']);
Route::post('/login', [UserController::class, 'Login']);
Route::post('/ask_otp', [UserController::class, 'askcodevalidateion']);
Route::post('/lost_pswd', [UserController::class, 'Lost_pswd']);
Route::post('/provider', [UserController::class, 'AuthProvider']);
Route::post('/test_otp', [UserController::class, 'Test_code_validation']);


Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('/affectation/addaffectation', [AffectationController::class, 'Affectation']);
    Route::post('/permission/addpermission', [AffectationController::class, 'create_permission']);
    Route::post('/permission/updatepermission/{id}', [AffectationController::class, 'update_permission']);
    Route::get('/permission/listpermission/{id}', [AffectationController::class, 'list_permissions']);
    Route::post('/permission/donnerPermission', [AffectationController::class, 'affecterPermission']);
    Route::get('/permission/list_permission', [AffectationController::class, 'List_PermissionsAccordees']);
    Route::post('/permission/retireracces', [AffectationController::class, 'RetirerAcces']);
    Route::delete('/users/deleteuser', [UserController::class, 'SupprimerUser']);
    Route::get('/users/listeUsers', [UserController::class, 'listeUsersAffecter']);
    Route::get('/users/User_organisation/{id}', [UserController::class, 'listeUsersParOrganisation']);
    Route::get('/users/getuserid/{id}',[UserController::class, 'getuserId']);
    Route::post('/users/updatepswd',[UserController::class, 'changePswdProfil']);
    Route::put('/users/editprofile/',[UserController::class, 'editProfile']);
    Route::put('/users/updateuser/{id}',[UserController::class, 'UpdateUser']);
    Route::post('/users/changepswdprofil',[UserController::class, 'changePswdProfil']);
    Route::post('/users/editimage',[UserController::class, 'editImage']);

    //les routes des permissions
    Route::post('/role/addrole/{id}', [RoleController::class, 'create']);
    Route::post('/role/updaterole/{id}', [RoleController::class, 'update']);
    Route::post('/role/deleterole/{id}', [RoleController::class, 'deleterole']);
    Route::get('/role/list/{id}', [RoleController::class, 'list_roles']);

    //les routes maladie
    Route::post('/maladie/addmaladie/{id}', [MaladieController::class, 'AddMaladie']);
    Route::post('/maladie/updatemaladie/{id}', [MaladieController::class, 'updateMaladie']);
    Route::get('/maladie/list/{id}', [MaladieController::class, 'listMaladie']);

    //les routes crises
    Route::post('/crise/addcrise', [CriseController::class, 'AddCrise']);
    Route::post('/crise/updatecrise/{id}', [CriseController::class, 'UpdateCrise']);
    Route::get('/crise/list', [CriseController::class, 'ListeCrise']);

    //les routes medicament
    Route::post('/medicament/addmedicament', [MedicamentController::class, 'AddMedicament']);
    Route::post('/medicament/UpdateMedicament/{id}', [MedicamentController::class, 'UpdateMedicament']);
    Route::get('/medicament/list', [MedicamentController::class, 'ListeMedicament']);

     //les routes type personnel
     Route::post('/personnel/addtypepersonnel', [PersonnelController::class, 'AddPersonel']);
     Route::post('/personnel/Updatetypepersonnel/{id}', [PersonnelController::class, 'UpdatePersonel']);
     Route::get('/personnel/list', [PersonnelController::class, 'ListePersonel']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    //Organisation et type organisation
    Route::post('/addtype', [OrganisationController::class, 'addtypeorg']);
    Route::get('/listtyp', [OrganisationController::class, 'listtype']);
    Route::post('/addorga', [OrganisationController::class, 'addOrg']);
    Route::post('/addindic', [OrganisationController::class, 'addindicateur']);
    Route::get('/org_ind', [OrganisationController::class, 'org_indicateur']);
    Route::get('/list_org', [OrganisationController::class, 'list_organisation']);
    Route::get('/liste_indicateur', [OrganisationController::class, 'listeIndicateur']);

    //Pyramide medical
    Route::post('/addprov', [Pyramide::class, 'addprovince']);
    Route::get('/listprovince', [Pyramide::class, 'listprovince']);
    Route::get('/listprovince_item', [Pyramide::class, 'all_province_item']);
    Route::get('/aire_item/{id}', [Pyramide::class, 'molo_up']);
    Route::post('/addter', [Pyramide::class, 'addterritoir']);
    Route::get('/listterritoir/{id}', [Pyramide::class, 'listterritoir']);
    Route::post('/addzon', [Pyramide::class, 'addzone']);
    Route::get('/listzon/{id}', [Pyramide::class, 'listzone']);
    Route::post('/addair', [Pyramide::class, 'addaire']);
    Route::get('/listair/{id}', [Pyramide::class, 'listaire']);
    Route::post('/structure/addstructure', [Pyramide::class, 'addstructure']);
    Route::get('/structure/liststructure/{id}', [Pyramide::class, 'liststructure_par_aire']);
    //Gap medical
    Route::post('/gap/sendGap', [GapsController::class, 'AddGap']);
    Route::post('/gap/validegap/{id}', [GapsController::class, 'valideGap']);
    Route::get('/gap/listgap/{id}', [GapsController::class, 'listGap']);
    Route::get('/gap/listgap_by_user', [GapsController::class, 'listGapByuser']);
    Route::get('/gap/listgap_valide_byuser',[GapsController::class, 'listGapValideByuser']);
    Route::get('/gap/listgap1', [GapsController::class, 'listgap1']);

    Route::get('/gap/listgap_province/{id}', [GapsController::class, 'listGapProvince']);
    Route::get('/gap/listgap_territoir/{id}', [GapsController::class, 'listGapTerritoir']);
    Route::get('/gap/listgap_zone/{id}', [GapsController::class, 'listGapZone']);
    Route::get('/gap/listgap_aire/{id}', [GapsController::class, 'listGapAire']);
    Route::get('/gap/detailgap/{id}', [GapsController::class, 'DetailGaps']);

    //Gap medical
    Route::post('/alert/sendAlert', [AlertController::class, 'sendAlert']);
    Route::post('/alert/updateAlert/{id}', [AlertController::class, 'updateAlert']);
    Route::put('/alert/valider_alert/{id}', [AlertController::class, 'validerAlert']);
    Route::post('/alert/suppressionalert/{id}', [AlertController::class, 'suppressionalert']);
    Route::get('/alert/listalert/{id}', [AlertController::class, 'getAlert']);
    Route::get('/alert/listalertvalide/{id}', [AlertController::class, 'getAlertvalide']);
    Route::get('/alert/detailAlert/{id}', [AlertController::class, 'getDetailAlert']);
    Route::get('/alert/alertbyuser/{id}', [AlertController::class, 'alertuser']);

     //ScoreCard
    Route::post('/scorecard/addentete_question', [ScoreCardController::class, 'AddEntete']);
    Route::get('/scorecard/listentete', [ScoreCardController::class, 'list_entete']);
    Route::post('/scorecard/addquestion', [ScoreCardController::class, 'addquestion']);
    Route::get('/scorecard/listequestion/{id}', [ScoreCardController::class, 'ListQuestionRubrique']);
    Route::post('/scorecard/sendscorecard',[ScoreCardController::class ,'sendscoreCard']);

    //Menage & personne
    Route::post('/menage/new_menage', [MenageController::class, 'create_menage']);
    Route::put('/menage/update_menage/{id}', [MenageController::class, 'updatemenage']);
    Route::post('/menage/new_personne', [MenageController::class, 'create_personne']);
    Route::post('/menage/update_personne/{id}', [MenageController::class, 'updatepersonne']);
    Route::get('/menage/list_typepersonne', [MenageController::class, 'listtypepersonne']);
    Route::get('/menage/liste_rolemenage', [MenageController::class, 'listerolemenage']);
    Route::get('/menage/liste_question', [MenageController::class, 'listequestion']);
    Route::get('/menage/list_menage', [MenageController::class, 'listmenage']);
    Route::get('/menage/code_menage/{code}', [MenageController::class, 'CodeMenage']);
    Route::get('/menage/detail_menage/{id}', [MenageController::class, 'DetailMenage']);
    Route::get('/menage/list_critere', [MenageController::class, 'listcritere']);

});
