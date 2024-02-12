<?php
use App\Http\Controllers\ActiviteController;
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
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\GapAppuiController;
use App\Http\Controllers\MenageController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\ScoreCardController;
use App\Http\Controllers\PublicationsController;
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
Route::get('/gap/getlastgapvalide',[GapsController::class, 'getlastgapvalide']);
Route::get('/alert/getlastalertvalide',[AlertController::class, 'getlastalertvalide']);
Route::post('/contact/getintouch',[PublicationsController::class, 'contact']);
Route::get('/configuration/get_infos_organisation',[ConfigurationController::class,'get_infos_organisation']);


Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('/affectation/addaffectation', [AffectationController::class, 'Affectation']);
    Route::post('/permission/addpermission', [AffectationController::class, 'create_permission']);
    Route::post('/permission/updatepermission/{id}', [AffectationController::class, 'update_permission']);
    Route::delete('/permission/deletepermission/{id}', [AffectationController::class, 'delete_permission']);
    Route::get('/permission/listpermission', [AffectationController::class, 'list_permissions']);
    Route::post('/permission/donnerPermission', [AffectationController::class, 'affecterPermission']);
    Route::get('/permission/list_permission/{id}', [AffectationController::class, 'List_PermissionsAccordees']);
    Route::post('/permission/retireracces', [AffectationController::class, 'RetirerAcces']);
    Route::put('/users/deleteuser/{userid}/{orgid}', [UserController::class, 'SupprimerUser']);
    Route::get('/users/listeUsers', [UserController::class, 'listeUsersAffecter']);
    Route::get('/users/User_organisation/{id}', [UserController::class, 'listeUsersParOrganisation']);
    Route::get('/users/getuserid/{id}',[UserController::class, 'getuserId']);
    Route::post('/users/updatepswd',[UserController::class, 'changePswdProfil']);
    Route::put('/users/editprofile/',[UserController::class, 'editProfile']);
    Route::put('/users/updateuser/{id}',[UserController::class, 'UpdateUser']);
    Route::post('/users/changepswdprofil',[UserController::class, 'changePswdProfil']);
    Route::post('/users/editimage',[UserController::class, 'editImage']);
    Route::get('/users/get_user',[UserController::class, 'getuser']);
    Route::post('/users/new_user', [UserController::class, 'NewUser']);



    //les routes des permissions
    Route::post('/role/addrole/{id}', [RoleController::class, 'create']);
    Route::post('/role/updaterole/{id}', [RoleController::class, 'updaterole']);
    Route::post('/role/deleterole/{id}', [RoleController::class, 'deleterole']);
    Route::get('/role/list/{id}', [RoleController::class, 'list_roles']);


    //les routes maladie
    Route::post('/maladie/addmaladie/{id}', [MaladieController::class, 'AddMaladie']);
    Route::post('/maladie/updatemaladie/{id}', [MaladieController::class, 'updateMaladie']);
    Route::get('/maladie/list', [MaladieController::class, 'listMaladie']);

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
    Route::post('/org/update_org/{id}', [OrganisationController::class, 'updateorganisation']);
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
    Route::post('/gap/sendImageGap/{id}', [GapsController::class, 'Imagegap']);
    Route::post('/gap/updategap/{id}', [GapsController::class, 'UpdateGap']);
    Route::delete('/gap/deletegap/{id}', [GapsController::class ,'deletegap']);
    Route::post('/gap/validegap/{id}', [GapsController::class, 'valideGap']);
    Route::get('/gap/listgap/{id}', [GapsController::class, 'listGap']);
    Route::get('/gap/listgap_by_user/{id}', [GapsController::class, 'listGapByuser']);
    Route::get('/gap/listgap_valide_byuser/{id}',[GapsController::class, 'listGapValideByuser']);
    Route::get('/gap/list_gap_validerepondu/{id}',[GapsController::class, 'listGapValideRepondu']);
    Route::get('/gap/listgap_valide/{id}',[GapsController::class, 'listGapValide']);
    Route::get('/gap/listgap1', [GapsController::class, 'listgap1']);

    Route::get('/gap/listgap_province/{id}', [GapsController::class, 'listGapProvince']);
    Route::get('/gap/listgap_territoir/{id}', [GapsController::class, 'listGapTerritoir']);
    Route::get('/gap/listgap_zone/{id}', [GapsController::class, 'listGapZone']);
    Route::get('/gap/listgap_aire/{id}', [GapsController::class, 'listGapAire']);
    Route::get('/gap/detailgap/{id}', [GapsController::class, 'DetailGaps']);

    //Alert
    Route::post('/alert/sendAlert', [AlertController::class, 'sendAlert']);
    Route::post('/alert/sendimageAlert/{id}', [AlertController::class, 'Imagealert']);
    Route::put('/alert/updateAlert/{id}', [AlertController::class, 'updateAlert']);
    Route::post('/alert/updateAlertMobile/{id}', [AlertController::class, 'updateAlert']);
    Route::post('/alert/suppressionalert/{id}', [AlertController::class, 'suppressionalert']);
    Route::post('/alert/rejetealert/{id}', [AlertController::class, 'rejetealert']);
    Route::put('/alert/valider_alert/{id}', [AlertController::class, 'validerAlert']);
    Route::post('/alert/valider_alertMobile/{id}', [AlertController::class, 'validerAlert']);
    Route::get('/alert/listalert/{id}', [AlertController::class, 'getAlert']);
    Route::get('/alert/listalertvalide/{id}', [AlertController::class, 'getAlertvalide']);
    Route::get('/alert/detailAlert/{id}', [AlertController::class, 'getDetailAlert']);
    Route::get('/alert/alertbyuser/{id}', [AlertController::class, 'alertuser']);
    Route::get('/alert/get_alert_valide_byuser/{id}', [AlertController::class, 'getAlertvalideByuser']);
    Route::get('/alert/get_alertinvalide_byuser/{id}',[AlertController::class,'getAlertInvalideByuser']);
    Route::get('/alert/get_all_alertinvalide/{id}', [AlertController::class,'getAlertInvalide']);

     //Publication
    Route::post('/publication/addpublication',[PublicationsController::class,'addpublication']);
    Route::get('/publication/getpublication',[PublicationsController::class,'getpublication']);


     //ScoreCard
    Route::post('/scorecard/addentete_question', [ScoreCardController::class, 'AddEntete']);
    Route::get('/scorecard/listentete', [ScoreCardController::class, 'list_entete']);
    Route::post('/scorecard/addquestion', [ScoreCardController::class, 'addquestion']);
    Route::get('/scorecard/listequestion/{id}', [ScoreCardController::class, 'ListQuestionRubrique']);
    Route::post('/scorecard/sendscorecard',[ScoreCardController::class ,'sendscoreCard']);

    //Menage & personne
    Route::post('/menage/new_menage', [MenageController::class, 'create_menage']);
    Route::post('/menage/deletemenage',[MenageController::class, 'delete_menage']);
    Route::put('/menage/update_menage/{id}', [MenageController::class, 'updatemenage']);
    Route::post('/menage/new_personne', [MenageController::class, 'create_personne']);
    Route::post('/menage/update_personne/{id}', [MenageController::class, 'updatepersonne']);
    Route::get('/menage/list_typepersonne', [MenageController::class, 'listtypepersonne']);
    Route::get('/menage/liste_question', [MenageController::class, 'listequestion']);
    Route::get('/menage/liste_rolemenage', [MenageController::class, 'listerolemenage']);
    Route::get('/menage/list_menage', [MenageController::class, 'listmenage']);
    Route::get('/menage/code_menage/{code}', [MenageController::class, 'CodeMenage']);
    Route::get('/menage/detail_menage/{id}', [MenageController::class, 'DetailMenage']);
    Route::get('/menage/list_critere', [MenageController::class, 'listcritere']);

    //Gestion des activitées de l'entrepise
    Route::post('/activite/create_activite',[ActiviteController::class, 'create_activite']);
    Route::post('/activite/update_activite/{id}',[ActiviteController::class, 'updateactivite']);
    Route::get('/activite/get_activite/{id}',[ProjetController::class, 'getactivites']);
    Route::get('/activite/detailactivite/{id}',[ActiviteController::class,'detailActivite']);
    Route::get('/activite/getcohp',[ActiviteController::class,'getcohp']);


     // Configuration afiagap
    Route::post('/configuration/create_infos_app',[ConfigurationController::class, 'create_infos_app']);
    Route::post('/configuration/create_logo_fiveicon',[ConfigurationController::class, 'create_logo_fiveicon']);

    //Gap_Appui
    Route::post('/gap_appui/create_gap_appui/{id}',[GapAppuiController::class, 'create_gap_appui']);
    Route::get('/gap_appui/get_type_gap',[GapAppuiController::class, 'get_type_gap']);
    Route::post('/gap_appui/add_type_gap',[GapAppuiController::class, 'add_type_gap']);
    Route::post('/positionnement/take_position',[GapAppuiController::class, 'PositionnementPartenaire']);

    //Projet
    Route::post('/projet/create_projet',[ProjetController::class, 'create_projet']);
    Route::put('/projet/update_projet/{id}',[ProjetController::class, 'update_projet']);
    Route::get('/projet/getprojet/{id}',[ProjetController::class,'getprojet']);
    Route::post('/projet/create_detail_projet/{id}',[ProjetController::class, 'create_detail_projet']);
    Route::post('/projet/create_pyramide_projet/{id}',[ProjetController::class, 'create_rayon_action_projet']);
    Route::get('/projet/getstructurebyprojet/{id}',[ProjetController::class, 'getStructureByProjet']);
    Route::get('/projet/gettypevaccin',[ProjetController::class, 'gettypevaccin']);
    Route::get('/projet/get_all_activites/{id}',[ProjetController::class, 'getactivites']);

    Route::get('/projet/gettype_projet',[ProjetController::class,'gettype_projet']);
    Route::get('/projet/gettype_impact',[ProjetController::class,'gettype_impact']);
    Route::get('/projet/getindicateur/{id}',[ProjetController::class,'getindicateur']);


});
