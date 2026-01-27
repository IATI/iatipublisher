<?php

namespace Tests\Feature;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class RegisterTest.
 */
class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Register page load tests.
     *
     * @return void
     */
    public function test_the_register_page_loads_successfully(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    /**
     * All required fields for publisher test.
     *
     * @return void
     */
    public function test_publisher_must_enter_all_required_fields(): void
    {
        $this->post('/verifyPublisher')
            ->assertStatus(200)
            ->assertJsonValidationErrors(['publisher_name', 'publisher_id', 'registration_agency', 'registration_number']);
    }

    /**
     * Publisher name and id mismatch test.
     *
     * @return void
     */
    public function test_publisher_name_mismatch(): void
    {
        $this->post('/verifyPublisher', [
            'publisher_name'      => 'test101',
            'publisher_id'        => env('IATI_YIPL_PUBLISHER_ID'),
            'registration_agency' => env('IATI_YIPL_REGISTRATION_AGENCY'),
            'registration_number' => env('IATI_YIPL_REGISTRATION_NUMBER'),
            'identifier'          => env('IATI_YIPL_REGISTRATION_AGENCY') . '-' . env('IATI_YIPL_REGISTRATION_NUMBER'),
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'publisher_error',
                'errors' => [
                    'publisher_name',
                ],
            ])
            ->assertJsonValidationErrors(['publisher_name']);
    }

    /**
     * Publisher iati id mismatch test.
     *
     * @return void
     */
    public function test_publisher_iati_id_mismatch(): void
    {
        $this->post('/verifyPublisher', [
            'publisher_name'      => env('IATI_YIPL_PUBLISHER_NAME'),
            'publisher_id'        => env('IATI_YIPL_PUBLISHER_ID'),
            'identifier'          => env('IATI_YIPL_REGISTRATION_AGENCY') . '-' . env('IATI_YIPL_REGISTRATION_NUMBER'),
            'registration_agency' => env('IATI_YIPL_REGISTRATION_AGENCY'),
            'registration_number' => 100,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'publisher_error',
                'errors' => [
                    'identifier',
                ],
            ])
            ->assertJsonValidationErrors(['identifier']);
    }

    /**
     * Publisher verify test.
     *
     * @return void
     */
    public function test_publisher_verified(): void
    {
        $this->post('/verifyPublisher', [
            'publisher_name'      => env('IATI_YIPL_PUBLISHER_NAME'),
            'publisher_id'        => env('IATI_YIPL_PUBLISHER_ID'),
            'registration_agency' => env('IATI_YIPL_REGISTRATION_AGENCY'),
            'registration_number' => env('IATI_YIPL_REGISTRATION_NUMBER'),
            'identifier'          => env('IATI_YIPL_REGISTRATION_AGENCY') . '-' . env('IATI_YIPL_REGISTRATION_NUMBER'),
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    /**
     * All required fields for admin test.
     *
     * @return void
     */
    public function test_admin_must_enter_all_required_fields(): void
    {
        $this->post('/register')
            ->assertStatus(200)
            ->assertJsonValidationErrors(['full_name', 'email', 'publisher_id']);
    }

    /**
     * Email unique test.
     *
     * @return void
     * @throws \Exception
     */
    public function test_email_must_be_unique(): void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $this->post('/register', [
            'full_name'             => Str::random(5),
            'email'                 => $org->user->email,
            'publisher_id'          => Str::random(5),
        ])
            ->assertStatus(200)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Register success test.
     *
     * @return void
     * @throws \Exception
     */
    public function test_successful_registration(): void
    {
        Role::factory()->create(['id' => 1]);

        $this->post('/register', [
            'publisher_id'          => Str::random(5),
            'publisher_name'        => Str::random(5),
            'country'               => null,
            'registration_agency'   => 'NP-SWC',
            'registration_number'   => 10101,
            'identifier'            => Str::random(5),
            'status'                => 'pending',
            'full_name'             => Str::random(5),
            'email'                 => 'test+1@gmail.com',
            'default_language'      => 'en',
        ])->assertJsonStructure([
            'success',
        ])->assertJson(
            [
                'success' => true,
            ]
        );
    }
}
