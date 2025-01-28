<?php

declare(strict_types=1);

namespace Tests\Feature\PageLoad;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Activity\Transaction;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class ActivityPageLoadTest.
 */
class ActivityPageLoadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @param $route
     * @return void
     *
     * @dataProvider activityUrl
     * @test
     */
    public function check_activity_page_load($route): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();
        $activity = Activity::factory()->has(Transaction::factory())->create([
            'org_id' => $org->id,
            'created_by' => $org->user->id,
            'updated_by' => $org->user->id,
        ]);
        $this->actingAs($org->user);
        $response = $this->get(route($route, ['id' => $activity->id, 'transactionId' => $activity->transactions[0]->id]));
        $response->assertStatus(200);
    }

    /**
     * List of activity urls.
     *
     * @return array
     */
    public function activityUrl(): array
    {
        return [
            ['admin.activities.index', 'Your Activities'],
            ['admin.activity.show'],
            ['admin.activity.title.edit'],
            ['admin.activity.status.edit'],
            ['admin.activity.scope.edit'],
            ['admin.activity.default-flow-type.edit'],
            ['admin.activity.default-finance-type.edit'],
            ['admin.activity.default-aid-type.edit'],
            ['admin.activity.default-tied-status.edit'],
            ['admin.activity.collaboration-type.edit'],
            ['admin.activity.capital-spend.edit'],
            ['admin.activity.related-activity.edit'],
            ['admin.activity.legacy-data.edit'],
            ['admin.activity.description.edit'],
            ['admin.activity.date.edit'],
            ['admin.activity.recipient-country.edit'],
            ['admin.activity.humanitarian-scope.edit'],
            ['admin.activity.sector.edit'],
            ['admin.activity.conditions.edit'],
            ['admin.activity.country-budget-items.edit'],
            ['admin.activity.policy-marker.edit'],
            ['admin.activity.recipient-region.edit'],
            ['admin.activity.tag.edit'],
            ['admin.activity.other-identifier.edit'],
            ['admin.activity.identifier.edit'],
            ['admin.activity.document-link.edit'],
            ['admin.activity.contact-info.edit'],
            ['admin.activity.location.edit'],
            ['admin.activity.participating-org.edit'],
            ['admin.activity.planned-disbursement.edit'],
            ['admin.activity.budget.edit'],
            ['admin.activity.reporting-org.edit'],
            ['admin.activity.default_values.edit'],
            ['admin.activity.transaction.create'],
            ['admin.activity.transaction.edit'],
        ];
    }
}
