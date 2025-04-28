<?php

use App\Http\Controllers\Admin\ImportActivity\ImportActivityController;
use App\Http\Controllers\Admin\ImportActivity\ImportXlsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Setting Routes
|--------------------------------------------------------------------------
|
| Here is where you can register setting routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/
Route::group(['middleware' => ['can:crud_activity']], static function () {
    Route::get('/import', [ImportActivityController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportActivityController::class, 'store'])->name('import');
    Route::get('/import/list', [ImportActivityController::class, 'status'])->name('import.list');
    /**Polling api hit during progress bar*/
    Route::get('/import/get-import-list-data', [ImportActivityController::class, 'getImportListData'])->name('import.check.status');
    Route::get('/import/check-ongoing-import', [ImportActivityController::class, 'checkOngoingImport'])->name('import.check.ongoingImport');
    Route::delete('/import/delete-ongoing-import', [ImportActivityController::class, 'deleteOngoingImports'])->name('import.delete.ongoingImport');

    Route::post('/import/activity', [ImportActivityController::class, 'importValidatedActivities'])->name('import.activity');

    Route::get('/import/download/csv', [ImportActivityController::class, 'downloadTemplate'])->name('import.template');
    Route::delete('/import/errors/{activityId}', [ImportActivityController::class, 'deleteImportError'])->name('import.delete.error');

    Route::get('/import/xls', [ImportXlsController::class, 'index'])->name('import.xls.index');
    Route::post('/import/xls', [ImportXlsController::class, 'store'])->name('import.xls');
    Route::post('/import/xls/activity', [ImportXlsController::class, 'importValidatedActivities'])->name('import.activity.xls');
    Route::get('/import/xls/check-xls-status', [ImportXlsController::class, 'checkXlsStatus'])->name('import.xls.progress_status');

    Route::get('/import/xls/poll-xls-import-progress', [ImportXlsController::class, 'checkImportInProgressForPolling'])->name('import.xls.status');
    Route::get('/import/xls/list', [ImportXlsController::class, 'show'])->name('import.xls.list');
    Route::delete('/import/xls', [ImportXlsController::class, 'deleteImportStatus'])->name('import.xls.status.delete');
});
