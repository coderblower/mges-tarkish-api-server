<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateMedicalTestController;
use App\Http\Controllers\CandidateSkillTestController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\FinalTestController;
use App\Http\Controllers\MedicalTestListController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PreSkilledTestController;
use App\Http\Controllers\QuotaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkillListController;
use App\Http\Controllers\SkillTestController;
use App\Http\Controllers\TestByCountryController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/optimize', function() {

    Artisan::call('optimize');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    return "Cleared!";
});
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
//    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
//    Route::post('optimize', 'optimize');
    Route::post('send_otp', 'passResetOTP');
    Route::post('verify_otp', 'verifyOTP');
});
Route::get('/send-sms', [CandidateController::class, 'send_sms']);
Route::get('/table_upside_down', [UserController::class, 'approveCandidatesWithAllDocuments']);
Route::group(['name'=>'User','middleware' => 'api','prefix' => 'user'], function () {
    Route::post('/create', [UserController::class, 'create']);
    Route::post('/update', [UserController::class, 'update']);
    Route::post('/profile_update', [UserController::class, 'profileUpdate']);
    Route::post('/destroy', [UserController::class, 'destroy']);
    Route::post('/soft_destroy', [UserController::class, 'softDestroy']);
    Route::post('/all', [UserController::class, 'all']);
    Route::post('/get_user', [UserController::class, 'getUser']);
    Route::post('/get_user01', [UserController::class, 'getUser01']);
    Route::post('/count', [UserController::class, 'count']);
    Route::post('/group_by', [UserController::class, 'groupBy']);
    Route::post('/search_candidate', [UserController::class, 'searchCandidate']);
    Route::post('/search_candidate_two', [UserController::class, 'searchCandidate_test']);
    Route::post('/upload_verified_certificate', [UserController::class, 'uplaodVerifiedCertificate']);
    Route::post('/check_upload_verified_certificate', [UserController::class, 'checkUplaodVerifiedCertificate']);
});
Route::group(['name'=>'Role','middleware' => 'api','prefix' => 'role'], function () {
    Route::post('/all', [RoleController::class, 'all']);
});
Route::group(['name'=>'Partner','middleware' => 'api','prefix' => 'partner'], function () {
    Route::post('/create', [PartnerController::class, 'create']);
    Route::post('/update', [PartnerController::class, 'update']);
    Route::post('/quota_update', [PartnerController::class, 'quotaUpdate']);
    Route::post('/destroy', [PartnerController::class, 'destroy']);
    Route::post('/all', [PartnerController::class, 'all']);
    Route::post('/get_partners', [PartnerController::class, 'getPartners']);
});
Route::group(['name'=>'Agents','middleware' => 'api','prefix' => 'agent'], function () {
    Route::post('/percentages', [PartnerController::class, 'percentages']);
    Route::post('/quota_create', [QuotaController::class, 'create']);
    Route::post('/quota_update', [QuotaController::class, 'update']);
    Route::post('/quota_all', [QuotaController::class, 'all']);
});
Route::group(['name'=>'Candidate','middleware' => 'api','prefix' => 'candidate'], function () {
    Route::post('/create', [CandidateController::class, 'create']);
    Route::post('/update', [CandidateController::class, 'update']);
    Route::post('/update_pif', [CandidateController::class, 'updatePIF']);
    Route::post('/delete_pif', [CandidateController::class, 'deletePIF']);
    Route::post('/update_approval_status', [CandidateController::class, 'updateApprovalStatus']);
    Route::post('/destroy', [CandidateController::class, 'destroy']);
    Route::post('/all', [CandidateController::class, 'all']);
    Route::post('/get_all', [CandidateController::class, 'getAll']);
    Route::post('/candidate_by_creator', [CandidateController::class, 'candidateByCreator']);
    Route::post('/candidate_by_creator_count', [CandidateController::class, 'candidateByCreatorCount']);
    Route::post('/get_candidate', [CandidateController::class, 'getCandidateInfo']);
    Route::post('/get_candidate_by_id', [CandidateController::class, 'getCandidateById']);
    Route::post('/candidate_qr_save', [CandidateController::class, 'saveQr']);
});
Route::group(['name'=>'Payment','middleware' => 'api','prefix' => 'payment'], function () {
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/make-payment', [PaymentController::class, 'store']);
});
Route::group(['name'=>'Skill List','middleware' => 'api','prefix' => 'skill_list'], function () {
    Route::post('/create', [SkillListController::class, 'create']);
    Route::post('/update', [SkillListController::class, 'update']);
    Route::post('/destroy', [SkillListController::class, 'destroy']);
    Route::post('/all', [SkillListController::class, 'all']);
});
Route::group(['name'=>'Candidate Skill Test','middleware' => 'api','prefix' => 'candidate_skill_test'], function () {
    Route::post('/create', [CandidateSkillTestController::class, 'create']);
    Route::post('/update', [CandidateSkillTestController::class, 'update']);
    Route::post('/destroy', [CandidateSkillTestController::class, 'destroy']);
    Route::post('/all', [CandidateSkillTestController::class, 'all']);
});
Route::group(['name'=>'Pre Skill Test','middleware' => 'api','prefix' => 'pre_skill_test'], function () {
    Route::post('/create', [PreSkilledTestController::class, 'create']);
    Route::post('/update', [PreSkilledTestController::class, 'update']);
    Route::post('/destroy', [PreSkilledTestController::class, 'destroy']);
    Route::post('/all', [PreSkilledTestController::class, 'all']);
});
Route::group(['name'=>'Skill Test','middleware' => 'api','prefix' => 'skill_test'], function () {
    Route::post('/create', [SkillTestController::class, 'create']);
    Route::post('/update', [SkillTestController::class, 'update']);
    Route::post('/destroy', [SkillTestController::class, 'destroy']);
    Route::post('/all', [SkillTestController::class, 'all']);
    Route::post('/all0', [SkillTestController::class, 'all0']);
    Route::post('/all1', [SkillTestController::class, 'all1']);
});
Route::group(['name'=>'Final Test','middleware' => 'api','prefix' => 'final_test'], function () {
    Route::post('/create', [FinalTestController::class, 'create']);
    Route::post('/update', [FinalTestController::class, 'update']);
    Route::post('/destroy', [FinalTestController::class, 'destroy']);
    Route::post('/all', [FinalTestController::class, 'all']);
    Route::post('/all0', [FinalTestController::class, 'all0']);
    Route::post('/all1', [FinalTestController::class, 'all1']);
    Route::post('/training_centers', [FinalTestController::class, 'getTrainingCenters']);
    Route::post('/filter', [FinalTestController::class, 'filterTrainingReport']);
});
Route::group(['name'=>'Medical Test List','middleware' => 'api','prefix' => 'medical_test_list'], function () {
    Route::post('/create', [MedicalTestListController::class, 'create']);
    Route::post('/update', [MedicalTestListController::class, 'update']);
    Route::post('/destroy', [MedicalTestListController::class, 'destroy']);
    Route::post('/all', [MedicalTestListController::class, 'all']);
});
Route::group(['name'=>'Test By Country List','middleware' => 'api','prefix' => 'test_by_country'], function () {
    Route::post('/create', [TestByCountryController::class, 'create']);
    Route::post('/update', [TestByCountryController::class, 'update']);
    Route::post('/destroy', [TestByCountryController::class, 'destroy']);
    Route::post('/all', [TestByCountryController::class, 'all']);
});
Route::group(['name'=>'Candidate Medical Test','middleware' => 'api','prefix' => 'candidate_medical_test'], function () {
    Route::post('/create', [CandidateMedicalTestController::class, 'create']);
    Route::post('/update', [CandidateMedicalTestController::class, 'update']);
    Route::post('/destroy', [CandidateMedicalTestController::class, 'destroy']);
    Route::post('/all', [CandidateMedicalTestController::class, 'all']);
    Route::post('/count', [CandidateMedicalTestController::class, 'count']);
    Route::post('/filter', [CandidateMedicalTestController::class, 'filterMedicalReport']);
    Route::post('/medical_report_data', [CandidateMedicalTestController::class, 'reportData']);
});
Route::group(['name'=>'Country','middleware' => 'api','prefix' => 'country'], function () {
    Route::post('/create', [CountryController::class, 'create']);
    Route::post('/update', [CountryController::class, 'update']);
    Route::post('/destroy', [CountryController::class, 'destroy']);
    Route::post('/all', [CountryController::class, 'all']);
});
Route::group(['name'=>'Designation','middleware' => 'api','prefix' => 'designation'], function () {
    Route::post('/create', [DesignationController::class, 'create']);
//    Route::post('/update', [CountryController::class, 'update']);
//    Route::post('/destroy', [CountryController::class, 'destroy']);
    Route::post('/all', [DesignationController::class, 'all']);
});

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

