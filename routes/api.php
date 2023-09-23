<?php

use Illuminate\Http\Request;
use App\Models\TalentRecruitment;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TermController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ToeflShiftController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\BomDivisionController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\PartnershipController;
use App\Http\Controllers\TalentFieldController;
use App\Http\Controllers\ToeflDetailController;
use App\Http\Controllers\ToeflPaymentController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactPersonController;
use App\Http\Controllers\MemberPaymentController;
use App\Http\Controllers\BomRecruitmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomingPartyController;
use App\Http\Controllers\PaymentProviderController;
use App\Http\Controllers\ToeflAttendanceController;
use App\Http\Controllers\RegistrantDetailController;
use App\Http\Controllers\TalentRecruitmentController;
use App\Http\Controllers\WelcomingPartyShiftController;
use App\Http\Resources\users\UserResource;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return  new UserResource($request->user());
});

//Auth
Route::controller(LoginController::class)->name('login.')->group(function(){
    Route::post('/login', 'login')->name('login');
    Route::get('/login', 'unauthorizedMessage')->name('unauthorized-message');
    Route::post('/logout',  'logout')->middleware('auth:api');
});

Route::controller(RegisterController::class)->prefix('register')->name('register.')->group(function(){
    Route::post('store', 'store')->name('store');
    Route::post('extend', 'storeExtend')->name('store-extend');
    Route::post('member', 'storeMember')->name('store-member');
    Route::get('validate-environment', 'validateEnvironment')->name('validate-environment');
});

Route::apiResource('terms',TermController::class);
Route::apiResource('regions',RegionController::class);
Route::apiResource('majors',MajorController::class);
Route::apiResource('achievements',AchievementController::class);
Route::apiResource('partnerships',PartnershipController::class);
Route::apiResource('contact-persons',ContactPersonController::class);
Route::apiResource('payment-providers',PaymentProviderController::class);

//Toefl Shift 
Route::get('toefl-shifts/{toeflShift}/update-visibility', [ToeflShiftController::class,'updateVisibility'])->name('toefl-shifts.update-visibility');
Route::apiResource('toefl-shifts',ToeflShiftController::class);

// TOEFL Payment 
Route::controller(ToeflPaymentController::class)->prefix('toefl-payments')->name('toefl-payments.')->group(function(){
    Route::get('get-confirmeds/{term}','getConfirmeds')->name('get-confirmeds');
    Route::get('get-pendings/{term}','getPendings')->name('get-pendings');
    Route::get('get-recycleds/{term}','getRecycleds')->name('get-recycleds');
    Route::get('confirm/{toeflPayment}','confirm')->name('confirm');
    Route::get('cancel/{toeflPayment}','cancel')->name('cancel');
    Route::get('restore/{toeflPayment}','restore')->name('restore');
    Route::get('delete/{toeflPayment}','delete')->name('delete');
    Route::get('export/{term?}','export')->name('export');
    Route::get('view-receipt/{toeflDetail}','viewReceipt')->name('receipt');
    
});
Route::apiResource('toefl-payments',ToeflPaymentController::class);

//TOEFL Detail
Route::controller(ToeflDetailController::class)->prefix('toefl-details')->name('toefl-details.')->group(function(){
    Route::get('get-all/{term}','getAll')->name('get-all');
    Route::get('get-recycleds/{term}','getRecycleds')->name('get-recycleds');
    Route::get('get-edit-requests','getEditRequests')->name('get-edit-requests');
    Route::get('get-confirmeds/{shift}','getConfirmeds')->name('get-confirmeds');
    Route::get('get-pendings/{shift}','getPendings')->name('get-pendings');
    Route::get('confirm/{toeflDetail}','confirm')->name('confirm');
    Route::get('reject/{toeflDetail}','reject')->name('reject');
    Route::put('update-detail/{toeflDetail}','updateDetail')->name('update-detail');
    Route::get('restore/{toeflDetail}','restore')->name('restore');
    Route::get('delete/{toeflDetail}','delete')->name('delete');
    Route::get('export/{term?}','export')->name('export');
    Route::get('validate-environment','validateEnvironment')->name('validate-enviroment');
});
Route::apiResource('toefl-details',ToeflDetailController::class);

// TOEFL Attendance
Route::get('toefl-attendances/get-all/{term}', [ToeflAttendanceController::class,'getAll'])->name('toefl-attendances.get-all');
Route::get('toefl-attendances/export', [ToeflAttendanceController::class,'export'])->name('toefl-attendances.export');
Route::apiResource('toefl-attendances',ToeflAttendanceController::class);

// Talent Field 
Route::apiResource('talent-fields',TalentFieldController::class);

//Talent Recruitment
Route::controller(TalentRecruitmentController::class)->prefix('talent-recruitments')->name('talent-recruitments.')->group(function(){
    Route::get('{term}/export','export')->name('export');
    Route::get('{talentRecruitment}/restore','restore')->name('restore');
    Route::get('{talentRecruitment}/delete','delete')->name('delete');
    Route::get('validate-environment','validateEnvironment')->name('validate-environment');
});
Route::apiResource('talent-recruitments',TalentRecruitmentController::class);


//Member Payment 
Route::controller(MemberPaymentController::class)->prefix('member-payments')->name('member-payments.')->group(function(){
    Route::get('get-confirmeds/{term}','getConfirmeds')->name('get-confirmeds');
    Route::get('get-pendings/{term}','getPendings')->name('get-pendings');
    Route::get('get-recycleds/{term}','getRecycleds')->name('get-recycleds');
    Route::get('{memberPayment}/cancel','cancelConfirmation')->name('cancel');
    Route::get('{memberPayment}/confirm','confirm')->name('confirm');
    Route::get('{memberPayment}/export','export')->name('export');
    Route::put('{memberPayment}/edit-by-user','editByUser')->name('edit-by-user');
    Route::get('{memberPayment}/restore','restore')->name('restore');
    Route::get('{memberPayment}/delete','delete')->name('delete');
    Route::get('validate-environment','validateEnvironment')->name('validate-environment');
    Route::get('{id}/view-receipt','viewReceipt')->name('view-receipt');
});
Route::apiResource('member-payments',MemberPaymentController::class);

// BOM Recruitment 
Route::controller(BomRecruitmentController::class)->prefix('bom-recruitments')->name("bom-recruitments.")->group(function(){
    Route::get('get-all/{term}','getAll')->name('get-all');
    Route::get('get-recycleds/{term}','getRecycleds')->name('get-recycleds');
    Route::get('get-summaries/{term}','getSummaries')->name('get-summaries');
    Route::get('{term}-{region}/export','export')->name('export');
    Route::get('{bomRecruitment}/restore','restore')->name('restore');
    Route::get('{bomRecruitment}/delete','delete')->name('delete');
    Route::get('validate-environment','validateEnvironment')->name('validate-environment');
    
});
Route::apiResource('bom-recruitments',BomRecruitmentController::class);

//BOM Division 
Route::apiResource('bom-divisions',BomDivisionController::class);


//Welcoming Party Shift
Route::apiResource('welcome-party-shifts',WelcomingPartyShiftController::class);


//Welcoming Party Attendance
Route::controller(WelcomingPartyController::class)->prefix('welcoming-parties')->name("welcoming-parties.")->group(function(){
    Route::get('export','export')->name('export');
    Route::get('confirm-all','confirmAll')->name('confirm-all');
    Route::get('{welcomingParty}/delete','delete')->name('delete');
    Route::get('{welcomingParty}/confirm','confirm')->name('confirm');
    Route::get('{welcomingParty}/restore','restore')->name('confirm');
});
Route::apiResource('welcoming-parties', WelcomingPartyController::class);


//Registrant Detail
Route::get('registrant-details/get-all/{term}', [RegistrantDetailController::class,'getAll'])->name('registrant-details.get-all');
Route::get('registrant-details/export', [RegistrantDetailController::class,'export'])->name('registrant-details.export');
Route::apiResource('registrant-details', RegistrantDetailController::class);

//Environment
Route::apiResource('environments', EnvironmentController::class);
Route::get('registrant-details/export/{term}', [RegistrantDetailController::class,'export'])->name('registrant-details.export');
Route::apiResource('registrant-details', RegistrantDetailController::class);

//Dashboard Admin
Route::get('dashboard-admin/{term}', [DashboardController::class, 'search'])->name('dashboard-admin.search');
