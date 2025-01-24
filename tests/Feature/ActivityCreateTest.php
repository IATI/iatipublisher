<?php

namespace Tests\Feature;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * All required fields for creating activity test.
     *
     * @return void
     */
    public function test_user_must_enter_all_fields_for_creating_activity(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();
        Activity::factory()->create([
            'org_id'     => $org->id,
            'created_by' => $org->user->id,
            'updated_by' => $org->user->id,
        ]);
        $this->actingAs($org->user)->post('/activity', [])
            ->assertSessionHasErrors(['narrative', 'activity_identifier']);
    }

    /**
     * Activity identifier must be unique for a particular organization.
     *
     * @return void
     */
    public function test_activity_identifier_must_be_unique_for_organization(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();
        Activity::factory()->create([
            'org_id' => $org->id,
            'created_by' => $org->user->id,
            'updated_by' => $org->user->id,
        ]);

        $this->actingAs($org->user)->post('/activity', [
            'narrative' => 'Test text',
            'language' => 'en',
            'activity_identifier' => 'SYRZ000041',
            'iati_identifier_text' => 'NP-SWC-0987-SYRZ000042',

        ])
            ->assertStatus(302)
            ->assertSessionHasErrors('activity_identifier');
    }

    /**
     * Create Activity success test.
     *
     * @return void
     */
    public function test_successful_activity_creation(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $this->actingAs($org->user)->post('/activity', [
            'narrative'            => Str::random(5),
            'language'             => 'en',
            'activity_identifier'  => '11111',
            'iati_identifier_text' => 'NP-SWC-0987-11111',
            'org_id'               => $org['id'],
            'created_by'           => $org->user->id,
            'updated_by'           => $org->user->id,
        ])
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_normally_create_activity_if_no_leading_whitespace_in_activity_identifier(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $response = $this->actingAs($org->user)->post('/activity', [
            'narrative'            => Str::random(5),
            'language'             => 'en',
            'activity_identifier'  => '11111',
            'iati_identifier_text' => 'NP-SWC-0987-11111',
            'org_id'               => $org['id'],
            'created_by'           => $org->user->id,
            'updated_by'           => $org->user->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_throws_error_when_identifier_has_leading_space(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $response = $this->actingAs($org->user)->post('/activity', [
            'narrative'            => Str::random(5),
            'language'             => 'en',
            'activity_identifier'  => ' 11111',
            'iati_identifier_text' => 'NP-SWC-0987- 11111',
            'org_id'               => $org['id'],
            'created_by'           => $org->user->id,
            'updated_by'           => $org->user->id,
        ]);

        $response->assertSessionHasErrors([
            'activity_identifier' => trans('validation.no_leading_white_space_in_activity_identifier'),
        ]);
    }

    public function test_can_normally_create_activity_if_no_spaces_in_between_activity_identifier(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $response = $this->actingAs($org->user)->post('/activity', [
            'narrative'            => Str::random(5),
            'language'             => 'en',
            'activity_identifier'  => '11-111',
            'iati_identifier_text' => 'NP-SWC-0987-11-111',
            'org_id'               => $org['id'],
            'created_by'           => $org->user->id,
            'updated_by'           => $org->user->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_throws_error_when_identifier_has_between(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $response = $this->actingAs($org->user)->post('/activity', [
            'narrative'            => Str::random(5),
            'language'             => 'en',
            'activity_identifier'  => '11 111',
            'iati_identifier_text' => 'NP-SWC-0987-11 111',
            'org_id'               => $org['id'],
            'created_by'           => $org->user->id,
            'updated_by'           => $org->user->id,
        ]);

        $response->assertSessionHasErrors([
            'activity_identifier' => trans('validation.no_spaces_in_between_in_activity_identifier'),
        ]);
    }
}
