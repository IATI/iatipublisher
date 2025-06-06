<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Database\Seeders\Fakers\ActivityTableFaker;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

/*
 * Class DashboardActivityApiTest
 */
class DashboardActivityApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private User $user;

    /**
     * Setup method.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->getSuperadmin();
        $this->fillSomeActivities();
    }

    /**
     * Test status 200 and data structure in dashboard data grouped by status api.
     *
     * @return void
     */
    public function test_dashboard_activity_api__for_stats()
    {
        $response = $this->actingAs($this->user)->get(url('/dashboard/activity/stats'));

        $response->assertStatus(200)->assertJsonStructure(
            [
                'totalCount',
                'lastUpdatedPublisher',
                'lastUpdatedActivity',
                'userId',
                'publisherWithoutActivity',
            ],
            $response['data']
        );
    }

    /**
     * Test status 200 and data structure in dashboard data grouped by data_licence api.
     *
     * @return void
     */
    public function test_dashboard_activity_api_for_count_loads()
    {
        $response = $this->actingAs($this->user)->get(url('/dashboard/activity/count'));

        $response->assertStatus(200);
    }

    /**
     * Test api accurately gives count.
     *
     * @return void
     */
    public function test_dashboard_activity_api_for_count_gives_correct_count()
    {
        $response = $this->actingAs($this->user)->get(url('/dashboard/activity/count'));

        $expectedCount = 25;
        $testCount = Arr::get($response, 'data.count', 0);

        $this->assertTrue($expectedCount === $testCount);
    }

    /**
     * Test status 200 and data structure in dashboard data grouped by country api.
     *
     * @return void
     */
    public function test_dashboard_activity_api_for_status()
    {
        $response = $this->actingAs($this->user)->get(url('/dashboard/activity/status'));

        $response->assertStatus(200)->assertJsonStructure([
            'Published',
            'Draft (Not Published)',
            'Draft (Need to republish)',
            ], $response['data']);
    }

    /**
     * Test status 200 and data structure in dashboard data grouped by registration type api.
     *
     * @return void
     */
    public function test_dashboard_activity_api_for_method()
    {
        $response = $this->actingAs($this->user)->get(url('/dashboard/activity/method'));

        $response->assertStatus(200)->assertJsonStructure([
            'By importing via CSV',
            'By importing via XML',
            'By importing via XLS',
            'Manually',
        ], $response['data']);
    }

    /**
     * Test status 200 and data structure in dashboard data grouped by registration count api.
     *
     * @return void
     */
    public function test_dashboard_activity_api_for_completeness()
    {
        $response = $this->actingAs($this->user)->get(url('/dashboard/activity/completeness'));

        $response->assertStatus(200)->assertJsonStructure([
            'Activities with complete core element data',
            'Activities with incomplete core element data',
        ], $response['data']);
    }

    /**
     * Test status 200 at download endpoint.
     *
     * @return void
     */
    public function test_dashboard_activity_api_for_download()
    {
        $response = $this->actingAs($this->user)->get(url('/dashboard/activity/download'));

        $response->assertDownload();
    }

    /**
     * @return User
     */
    private function getSuperadmin(): User
    {
        $role = Role::factory(['role'=>'superadmin'])->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->reportingOrg()->create();

        return $org->user;
    }

    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    private function fillSomeActivities(): void
    {
        app()->make(ActivityTableFaker::class)->run();
    }
}
