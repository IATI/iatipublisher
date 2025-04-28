<?php

use App\Http\Controllers\Admin\Activity\ActivityController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(RedirectIfAuthenticated::class)->name('web.')->group(function () {
    Route::get('/', [App\Http\Controllers\Web\WebController::class, 'index']);
    Route::get('/login', [App\Http\Controllers\Web\WebController::class, 'index'])->name('index.login');
    Route::get('/register/{page}', [App\Http\Controllers\Web\WebController::class, 'index'])->name('join');
    Route::get('/register', [App\Http\Controllers\Web\WebController::class, 'register'])->name('register');
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::get('/password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.email');
    Route::post('/password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendCustomPasswordResetNotification'])->name('password.email.post');
    Route::get('/password/confirm', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showEmailSentMessage'])->name('password.confirm');
    Route::get('/iati/register', [App\Http\Controllers\Auth\IatiRegisterController::class, 'showRegistrationForm'])->name('iati.register');
});

Route::group(['middleware' => ['guest', 'sanitize'], 'name' => 'web.'], static function () {
    Route::post('/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('reset');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
    Route::post('/verifyPublisher', [App\Http\Controllers\Auth\RegisterController::class, 'verifyPublisher'])->name('verify-publisher');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
    Route::post('/iati/register/publisher', [App\Http\Controllers\Auth\IatiRegisterController::class, 'verifyPublisher'])->name('iati.verify-publisher');
    Route::post('/iati/register/contact', [App\Http\Controllers\Auth\IatiRegisterController::class, 'verifyContactInfo'])->name('iati.verify-contact');
    Route::post('/iati/register/additional', [App\Http\Controllers\Auth\IatiRegisterController::class, 'verifyAdditionalInfo'])->name('iati.verify-source');
    Route::post('/iati/register', [App\Http\Controllers\Auth\IatiRegisterController::class, 'register'])->name('iati.user.register');
});

Route::middleware(RedirectIfAuthenticated::class)->get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/about', [App\Http\Controllers\Web\WebController::class, 'about'])->name('about');
Route::get('/publishing-checklist', [App\Http\Controllers\Web\WebController::class, 'publishingChecklist'])->name('publishingchecklist');
Route::get('/iati-standard', [App\Http\Controllers\Web\WebController::class, 'iatiStandard'])->name('iatistandard');
Route::get('/support', [App\Http\Controllers\Web\WebController::class, 'support'])->name('support');

Route::get('/activities/activities_count_by_published_status', [ActivityController::class, 'getActivitiesCountByPublishedStatus'])
    ->middleware('auth')
    ->name('activities.getActivitiesCountByPublishedStatus');
Route::get('/duplicate-activity', [ActivityController::class, 'duplicateActivity'])->middleware('auth');
Route::get('/language/{language}', [App\Http\Controllers\Web\WebController::class, 'setLocale'])->name('set-locale');
Route::get('/current-language', [App\Http\Controllers\Web\WebController::class, 'getLocale'])->name('get-locale');
Route::get('/translated-data', [App\Http\Controllers\Web\WebController::class, 'getTranslatedData'])->name('get-translated-data');

Route::get('/php-info', function () {
    dd(phpinfo());
})->middleware('superadmin')->name('php-info');

Route::get('xls-test', function () {
    try {
        /** @var $importXlsService \App\IATI\Services\ImportActivity\ImportXlsService */
        $importXlsService = app(App\IATI\Services\ImportActivity\ImportXlsService::class);

        //        $contents = json_decode(awsGetFile('XlsImporter/tmp/183/257/valid.json'), false, 512, JSON_THROW_ON_ERROR | 0);

        $activities = [
            '0'  => 0,
            '1'  => 1,
            '2'  => 2,
            '3'  => 3,
            '4'  => 4,
            '5'  => 5,
            '6'  => 6,
            '7'  => 7,
            '8'  => 8,
            '9'  => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12,
            '13' => 13,
            '14' => 14,
            '15' => 15,
            '16' => 16,
            '17' => 17,
            '18' => 18,
            '19' => 19,
            '20' => 20,
            '21' => 21,
            '22' => 22,
            '23' => 23,
            '24' => 24,
        ];
        $importXlsService->saveActivities($activities);
        dd('a');
    } catch (Exception $e) {
        dd($e);
        logger()->error($e);
    }
});
