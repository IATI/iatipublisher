<?php

use App\Http\Controllers\Admin\Organization\OrganizationController;
use App\Http\Controllers\ApiLog\ApiLogController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
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
Route::group(['namespace' => 'SuperAdmin', 'middleware' => ['can:list_organizations', 'can:proxy_user']], function () {
    Route::get('/list-organisations', [SuperAdminController::class, 'listOrganizations'])->name('listOrganizations');
    Route::get('/list-organisations/page/{page?}', [SuperAdminController::class, 'getPaginatedOrganizations'])->name('listOrganizations.paginate');
    Route::get('/proxy-organisation/{userId}/{orgId?}', [SuperAdminController::class, 'proxyOrganization'])->name('proxyOrganization');
    Route::get('/system-version', [SuperAdminController::class, 'listSystemVersion'])->name('systemVersion');
    Route::get('/api-log', [ApiLogController::class, 'getData'])->name('getData');
    Route::delete('/organization/{orgId}', [OrganizationController::class, 'destroy'])->name('organisation.deleteOrganization');
});
