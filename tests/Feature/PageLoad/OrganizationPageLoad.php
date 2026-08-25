<?php

declare(strict_types=1);

namespace Tests\Feature\PageLoad;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class OrganizationPageLoadTest.
 */
class OrganizationPageLoad extends TestCase
{
    use RefreshDatabase;

    /*
     * Ensure 200 status code on organization page load
     *
     * @return void
     * @test
     * @dataProvider organization_page_url
     */
    public function organization_page_load_test($route): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();
        $this->actingAs($org->user);
        $response = $this->get(route($route));
        $response->assertStatus(200);
    }

    /*
     * Organization page url
     *
     * @return array
     */
    public static function organization_page_url(): array
    {
        return [
            ['admin.organisation.index'],
            ['admin.organisation.name.edit'],
            ['admin.organisation.identifier.edit'],
            ['admin.organisation.reporting-org.edit'],
            ['admin.organisation.total-budget.edit'],
            ['admin.organisation.recipient-org-budget.edit'],
            ['admin.organisation.recipient-region-budget.edit'],
            ['admin.organisation.recipient-country-budget.edit'],
            ['admin.organisation.total-expenditure.edit'],
            ['admin.organisation.document-link.edit'],
        ];
    }
}
