<?php

use App\Http\Controllers\Admin\Activity\ActivityController;
use App\Http\Controllers\Admin\Activity\ActivityDefaultController;
use App\Http\Controllers\Admin\Activity\BudgetController;
use App\Http\Controllers\Admin\Activity\CapitalSpendController;
use App\Http\Controllers\Admin\Activity\CollaborationTypeController;
use App\Http\Controllers\Admin\Activity\ConditionController;
use App\Http\Controllers\Admin\Activity\ContactInfoController;
use App\Http\Controllers\Admin\Activity\CountryBudgetItemController;
use App\Http\Controllers\Admin\Activity\DateController;
use App\Http\Controllers\Admin\Activity\DefaultAidTypeController;
use App\Http\Controllers\Admin\Activity\DefaultFinanceTypeController;
use App\Http\Controllers\Admin\Activity\DefaultFlowTypeController;
use App\Http\Controllers\Admin\Activity\DefaultTiedStatusController;
use App\Http\Controllers\Admin\Activity\DescriptionController;
use App\Http\Controllers\Admin\Activity\DocumentLinkController;
use App\Http\Controllers\Admin\Activity\HumanitarianScopeController;
use App\Http\Controllers\Admin\Activity\IdentifierController;
use App\Http\Controllers\Admin\Activity\IndicatorController;
use App\Http\Controllers\Admin\Activity\LegacyDataController;
use App\Http\Controllers\Admin\Activity\LocationController;
use App\Http\Controllers\Admin\Activity\OtherIdentifierController;
use App\Http\Controllers\Admin\Activity\ParticipatingOrganizationController;
use App\Http\Controllers\Admin\Activity\PeriodController;
use App\Http\Controllers\Admin\Activity\PlannedDisbursementController;
use App\Http\Controllers\Admin\Activity\PolicyMarkerController;
use App\Http\Controllers\Admin\Activity\RecipientCountryController;
use App\Http\Controllers\Admin\Activity\RecipientRegionController;
use App\Http\Controllers\Admin\Activity\RelatedActivityController;
use App\Http\Controllers\Admin\Activity\ReportingOrgController;
use App\Http\Controllers\Admin\Activity\ResultController;
use App\Http\Controllers\Admin\Activity\ScopeController;
use App\Http\Controllers\Admin\Activity\SectorController;
use App\Http\Controllers\Admin\Activity\StatusController;
use App\Http\Controllers\Admin\Activity\TagController;
use App\Http\Controllers\Admin\Activity\TitleController;
use App\Http\Controllers\Admin\Activity\TransactionController;
use App\Http\Controllers\Admin\Workflow\ActivityWorkflowController;
use Illuminate\Support\Facades\Route;

/*
 * README by @PG-Momik
 *
 * Activity Routes Group
 *
 * This group has the following middleware applied to it:
 *  - 'can:crud_activity' : This is visible right here in this file.
 *  - 'admin', 'auth', 'activity': These are applied from RouteServiceProvider.
 *
 * This group of routes also have a group prefix of : 'admin.'.
 *
 * SEE : \App\Providers\RouteServiceProvider for all the groups and middleware applied.
 * SEE : \App\Providers\RouteServiceProvider if you get 302 redirects for no apparent reason.
 *
 */
Route::group(['middleware' => ['can:crud_activity']], static function () {
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/page/{page?}', [ActivityController::class, 'getPaginatedActivities'])->name('activities.paginate');
    Route::get('/activities/codelists', [ActivityController::class, 'getLanguagesOrganization'])->name('activities.codelist');
    Route::resource('/activity', ActivityController::class)->except('index')->parameters(['activity' => 'id']);
    Route::get('activity/{id}/title', [TitleController::class, 'edit'])->name('activity.title.edit');
    Route::put('activity/{id}/title', [TitleController::class, 'update'])->name('activity.title.update');
    Route::get('activity/{id}/activity_status', [StatusController::class, 'edit'])->name('activity.status.edit');
    Route::put('activity/{id}/activity_status', [StatusController::class, 'update'])->name('activity.status.update');
    Route::get('activity/{id}/activity_scope', [ScopeController::class, 'edit'])->name('activity.scope.edit');
    Route::put('activity/{id}/activity_scope', [ScopeController::class, 'update'])->name('activity.scope.update');
    Route::get('activity/{id}/default_flow_type', [DefaultFlowTypeController::class, 'edit'])->name('activity.default-flow-type.edit');
    Route::put('activity/{id}/default_flow_type', [DefaultFlowTypeController::class, 'update'])->name('activity.default-flow-type.update');
    Route::get('activity/{id}/default_finance_type', [DefaultFinanceTypeController::class, 'edit'])->name('activity.default-finance-type.edit');
    Route::put('activity/{id}/default_finance_type', [DefaultFinanceTypeController::class, 'update'])->name('activity.default-finance-type.update');
    Route::get('activity/{id}/default_aid_type', [DefaultAidTypeController::class, 'edit'])->name('activity.default-aid-type.edit');
    Route::put('activity/{id}/default_aid_type', [DefaultAidTypeController::class, 'update'])->name('activity.default-aid-type.update');
    Route::get('activity/{id}/default_tied_status', [DefaultTiedStatusController::class, 'edit'])->name('activity.default-tied-status.edit');
    Route::put('activity/{id}/default_tied_status', [DefaultTiedStatusController::class, 'update'])->name('activity.default-tied-status.update');
    Route::get('activity/{id}/collaboration_type', [CollaborationTypeController::class, 'edit'])->name('activity.collaboration-type.edit');
    Route::put('activity/{id}/collaboration_type', [CollaborationTypeController::class, 'update'])->name('activity.collaboration-type.update');
    Route::get('activity/{id}/capital_spend', [CapitalSpendController::class, 'edit'])->name('activity.capital-spend.edit');
    Route::put('activity/{id}/capital_spend', [CapitalSpendController::class, 'update'])->name('activity.capital-spend.update');
    Route::get('activity/{id}/related_activity', [RelatedActivityController::class, 'edit'])->name('activity.related-activity.edit');
    Route::put('activity/{id}/related_activity', [RelatedActivityController::class, 'update'])->name('activity.related-activity.update');
    Route::get('activity/{id}/legacy_data', [LegacyDataController::class, 'edit'])->name('activity.legacy-data.edit');
    Route::put('activity/{id}/legacy_data', [LegacyDataController::class, 'update'])->name('activity.legacy-data.update');
    Route::get('activity/{id}/description', [DescriptionController::class, 'edit'])->name('activity.description.edit');
    Route::put('activity/{id}/description', [DescriptionController::class, 'update'])->name('activity.description.update');
    Route::get('activity/{id}/activity_date', [DateController::class, 'edit'])->name('activity.date.edit');
    Route::put('activity/{id}/activity_date', [DateController::class, 'update'])->name('activity.date.update');
    Route::get('activity/{id}/recipient_country', [RecipientCountryController::class, 'edit'])->name('activity.recipient-country.edit');
    Route::put('activity/{id}/recipient_country', [RecipientCountryController::class, 'update'])->name('activity.recipient-country.update');
    Route::get('activity/{id}/humanitarian_scope', [HumanitarianScopeController::class, 'edit'])->name('activity.humanitarian-scope.edit');
    Route::put('activity/{id}/humanitarian_scope', [HumanitarianScopeController::class, 'update'])->name('activity.humanitarian-scope.update');
    Route::get('activity/{id}/sector', [SectorController::class, 'edit'])->name('activity.sector.edit');
    Route::put('activity/{id}/sector', [SectorController::class, 'update'])->name('activity.sector.update');
    Route::get('activity/{id}/conditions', [ConditionController::class, 'edit'])->name('activity.conditions.edit');
    Route::put('activity/{id}/conditions', [ConditionController::class, 'update'])->name('activity.conditions.update');
    Route::get('activity/{id}/country_budget_items', [CountryBudgetItemController::class, 'edit'])->name('activity.country-budget-items.edit');
    Route::put('activity/{id}/country_budget_items', [CountryBudgetItemController::class, 'update'])->name('activity.country-budget-items.update');
    Route::get('activity/{id}/default_aid_type', [DefaultAidTypeController::class, 'edit'])->name('activity.default-aid-type.edit');
    Route::put('activity/{id}/default_aid_type', [DefaultAidTypeController::class, 'update'])->name('activity.default-aid-type.update');
    Route::get('activity/{id}/policy_marker', [PolicyMarkerController::class, 'edit'])->name('activity.policy-marker.edit');
    Route::put('activity/{id}/policy_marker', [PolicyMarkerController::class, 'update'])->name('activity.policy-marker.update');
    Route::get('activity/{id}/recipient_region', [RecipientRegionController::class, 'edit'])->name('activity.recipient-region.edit');
    Route::put('activity/{id}/recipient_region', [RecipientRegionController::class, 'update'])->name('activity.recipient-region.update');
    Route::get('activity/{id}/tag', [TagController::class, 'edit'])->name('activity.tag.edit');
    Route::put('activity/{id}/tag', [TagController::class, 'update'])->name('activity.tag.update');
    Route::get('activity/{id}/other_identifier', [OtherIdentifierController::class, 'edit'])->name('activity.other-identifier.edit');
    Route::put('activity/{id}/other_identifier', [OtherIdentifierController::class, 'update'])->name('activity.other-identifier.update');
    Route::get('activity/{id}/iati_identifier', [IdentifierController::class, 'edit'])->name('activity.identifier.edit');
    Route::put('activity/{id}/iati_identifier', [IdentifierController::class, 'update'])->name('activity.identifier.update');
    Route::get('activity/{id}/document_link', [DocumentLinkController::class, 'edit'])->name('activity.document-link.edit');
    Route::put('activity/{id}/document_link', [DocumentLinkController::class, 'update'])->name('activity.document-link.update');
    Route::get('activity/{id}/contact_info', [ContactInfoController::class, 'edit'])->name('activity.contact-info.edit');
    Route::put('activity/{id}/contact_info', [ContactInfoController::class, 'update'])->name('activity.contact-info.update');
    Route::get('activity/{id}/location', [LocationController::class, 'edit'])->name('activity.location.edit');
    Route::put('activity/{id}/location', [LocationController::class, 'update'])->name('activity.location.update');
    Route::get('activity/{id}/participating_org', [ParticipatingOrganizationController::class, 'edit'])->name('activity.participating-org.edit');
    Route::put('activity/{id}/participating_org', [ParticipatingOrganizationController::class, 'update'])->name('activity.participating-org.update');
    Route::get('activity/{id}/planned_disbursement', [PlannedDisbursementController::class, 'edit'])->name('activity.planned-disbursement.edit');
    Route::put('activity/{id}/planned_disbursement', [PlannedDisbursementController::class, 'update'])->name('activity.planned-disbursement.update');
    Route::get('activity/{id}/budget', [BudgetController::class, 'edit'])->name('activity.budget.edit');
    Route::put('activity/{id}/budget', [BudgetController::class, 'update'])->name('activity.budget.update');
    Route::get('activity/{id}/reporting_org', [ReportingOrgController::class, 'edit'])->name('activity.reporting-org.edit');
    Route::put('activity/{id}/reporting_org', [ReportingOrgController::class, 'update'])->name('activity.reporting-org.update');

    Route::resource('activity.transaction', TransactionController::class)->parameters(['activity' => 'id', 'transaction' => 'transactionId']);
    Route::get('/activity/{id}/transactions/page/{page?}', [TransactionController::class, 'getPaginatedTransactions'])->name('activity.transactions.paginate');
    Route::delete('/activity/{id}/transactions', [TransactionController::class, 'bulkDeleteTransactions'])->name('activity.transactions.bulkDelete');

    //Unpublish Activity
    Route::post('activity/{id}/unpublish', [ActivityWorkflowController::class, 'unpublish'])->name('activity.unpublish');

    Route::resource('activity.result', ResultController::class)->parameters(['activity' => 'id', 'result' => 'resultId']);
    Route::get('/activity/{id}/results/page/{page?}', [ResultController::class, 'getPaginatedResults'])->name('activity.results.paginate');
    Route::delete('/activity/{id}/results', [ResultController::class, 'bulkDeleteResults'])->name('activity.results.bulkDelete');

    Route::resource('result.indicator', IndicatorController::class)->parameters(['result' => 'id', 'indicator' => 'indicatorId']);
    Route::get('/result/{id}/indicators/page/{page?}', [IndicatorController::class, 'getPaginatedIndicators'])->name('result.indicators.paginate');

    Route::resource('indicator.period', PeriodController::class)->parameters(['indicator' => 'id', 'period' => 'periodId']);
    Route::get('/indicator/{id}/periods/page/{page?}', [PeriodController::class, 'getPaginatedPeriods'])->name('indicator.periods.paginate');

    Route::get('activity/{id}/default_values', [ActivityDefaultController::class, 'edit'])->name('activity.default_values.edit');
    Route::get('activity/{id}/default_values/data', [ActivityDefaultController::class, 'getActivityDefaultValues'])->name('activity.default_values.data');
    Route::put('activity/{id}/default_values', [ActivityDefaultController::class, 'update'])->name('activity.default_values.update');
});
