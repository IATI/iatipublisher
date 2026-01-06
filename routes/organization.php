<?php

use App\Http\Controllers\Admin\Organization\DocumentLinkController;
use App\Http\Controllers\Admin\Organization\NameController;
use App\Http\Controllers\Admin\Organization\OrganizationController;
use App\Http\Controllers\Admin\Organization\OrganizationOnboardingController;
use App\Http\Controllers\Admin\Organization\RecipientCountryBudgetController;
use App\Http\Controllers\Admin\Organization\RecipientOrgBudgetController;
use App\Http\Controllers\Admin\Organization\RecipientRegionBudgetController;
use App\Http\Controllers\Admin\Organization\ReportingOrgController;
use App\Http\Controllers\Admin\Organization\TotalBudgetController;
use App\Http\Controllers\Admin\Organization\TotalExpenditureController;
use App\Http\Controllers\Admin\Workflow\OrganizationWorkflowController;
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

Route::get('/organisation/agency/{country?}', [OrganizationController::class, 'getRegistrationAgency'])->name('organisation.get.agency');

Route::group(['middleware' => ['can:view_organization']], static function () {
    Route::get('/organisation', [OrganizationController::class, 'show'])->name('organisation.index');
    Route::get('organisation/status', [OrganizationController::class, 'getPublisherStatus'])->name('organisation.status');
    Route::post('organisation-onboarding/toggle-dont-show', [OrganizationOnboardingController::class, 'toggleDontShow'])->name('organisation.toggle-dont-show');
    Route::get('organisation-onboarding/complete-activity', [OrganizationOnboardingController::class, 'completeActivityForOnboarding'])->name('organisation.complete-activity-for-onboarding');
});

Route::group(['middleware' => ['can:crud_organization']], static function () {
    Route::get('organisation/name', [NameController::class, 'edit'])->name('organisation.name.edit');
    Route::get('organisation/name', [NameController::class, 'edit'])->name('organisation.name.edit');
    Route::put('organisation/name', [NameController::class, 'update'])->name('organisation.name.update');
    Route::get('organisation/reporting_org', [ReportingOrgController::class, 'edit'])->name('organisation.reporting-org.edit');
    Route::put('organisation/reporting_org', [ReportingOrgController::class, 'update'])->name('organisation.reporting-org.update');
    Route::get('organisation/total_budget', [TotalBudgetController::class, 'edit'])->name('organisation.total-budget.edit');
    Route::put('organisation/total_budget', [TotalBudgetController::class, 'update'])->name('organisation.total-budget.update');
    Route::get('organisation/recipient_org_budget', [RecipientOrgBudgetController::class, 'edit'])->name('organisation.recipient-org-budget.edit');
    Route::put('organisation/recipient_org_budget', [RecipientOrgBudgetController::class, 'update'])->name('organisation.recipient-org-budget.update');
    Route::get('organisation/recipient_region_budget', [RecipientRegionBudgetController::class, 'edit'])->name('organisation.recipient-region-budget.edit');
    Route::put('organisation/recipient_region_budget', [RecipientRegionBudgetController::class, 'update'])->name('organisation.recipient-region-budget.update');
    Route::get('organisation/recipient_country_budget', [RecipientCountryBudgetController::class, 'edit'])->name('organisation.recipient-country-budget.edit');
    Route::put('organisation/recipient_country_budget', [RecipientCountryBudgetController::class, 'update'])->name('organisation.recipient-country-budget.update');
    Route::get('organisation/total_expenditure', [TotalExpenditureController::class, 'edit'])->name('organisation.total-expenditure.edit');
    Route::put('organisation/total_expenditure', [TotalExpenditureController::class, 'update'])->name('organisation.total-expenditure.update');
    Route::get('organisation/document_link', [DocumentLinkController::class, 'edit'])->name('organisation.document-link.edit');
    Route::put('organisation/document_link', [DocumentLinkController::class, 'update'])->name('organisation.document-link.update');
    Route::post('organisation/publish', [OrganizationWorkflowController::class, 'publish'])->name('organisation.publish');
    Route::post('organisation/unpublish', [OrganizationWorkflowController::class, 'unPublish'])->name('organisation.unPublish');
    Route::get('organisation/checks-for-organisation-publish', [OrganizationWorkflowController::class, 'checksForOrganizationPublish'])->name('organisation.checks_for_publish');
    Route::delete('organisation/{element}', [OrganizationController::class, 'deleteElement'])->name('organisation.delete_element');
});
