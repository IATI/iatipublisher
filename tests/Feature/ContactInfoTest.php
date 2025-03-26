<?php

namespace Tests\Feature;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ContactInfoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function test_validation_pass_for_contact_info() : void
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $activity = Activity::create([
            'org_id'          => $org->id,
            'iati_identifier' => [
                'activity_identifier'             => 'SYRZ000041',
                'iati_identifier_text'            => 'CZ-ICO-25755277-SYRZ000041',
                'present_organization_identifier' => 'CZ-ICO-25755277',
            ],
            'title'           => [
                [
                    'narrative' => 'DGGF Track 3',
                    'language'  => 'en',
                ],
            ],
        ]);

        $postData = [
            'contact_info' => [
                [
                    'type' => '1',
                    'organisation' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'department' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'person_name' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'job_title' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'telephone' => [
                        '0' => ['telephone' => '+1(555)123-4567'],
                        '1' => ['telephone' => '15551234567'],
                        '2' => ['telephone' => '+44 20 7123 4567'],
                        '3' => ['telephone' => '+86 10 1234 5678'],
                        '4' => ['telephone' => '+(977)-9841234561'],
                        '5' => ['telephone' => '+1-555-123-4567'],
                        '6' => ['telephone' => '(02) 1234 5678'],
                        '14' => ['telephone' => '9841234567'],
                    ],
                    'email' => [
                        [
                            'email' => null,
                        ],
                    ],
                    'website' => [
                        [
                            'website' => null,
                        ],
                    ],
                    'mailing_address' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($org->user)->put("/activity/$activity->id/contact_info", $postData);
        $response->assertStatus(302);
    }

    public function test_contact_info_throws_validation_error_when_number_too_short()
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $activity = Activity::create([
            'org_id'          => $org->id,
            'iati_identifier' => [
                'activity_identifier'             => 'SYRZ000041',
                'iati_identifier_text'            => 'CZ-ICO-25755277-SYRZ000041',
                'present_organization_identifier' => 'CZ-ICO-25755277',
            ],
            'title'           => [
                [
                    'narrative' => 'DGGF Track 3',
                    'language'  => 'en',
                ],
            ],
        ]);

        $postData = [
            'contact_info' => [
                [
                    'type' => '1',
                    'organisation' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'department' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'person_name' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'job_title' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'telephone' => [
                        '0' => ['telephone' => '12345'],
                    ],
                    'email' => [
                        [
                            'email' => null,
                        ],
                    ],
                    'website' => [
                        [
                            'website' => null,
                        ],
                    ],
                    'mailing_address' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($org->user)->put("/activity/$activity->id/contact_info", $postData);

        $validationErrors = Arr::dot(session('errors')->toArray());
        $this->assertContains(trans('validation.activity_contact_info.telephone.min'), $validationErrors);
    }

    public function test_contact_info_throws_validation_error_when_number_is_not_numeric()
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $activity = Activity::create([
            'org_id'          => $org->id,
            'iati_identifier' => [
                'activity_identifier'             => 'SYRZ000041',
                'iati_identifier_text'            => 'CZ-ICO-25755277-SYRZ000041',
                'present_organization_identifier' => 'CZ-ICO-25755277',
            ],
            'title'           => [
                [
                    'narrative' => 'DGGF Track 3',
                    'language'  => 'en',
                ],
            ],
        ]);

        $postData = [
            'contact_info' => [
                [
                    'type' => '1',
                    'organisation' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'department' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'person_name' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'job_title' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'telephone' => [
                        '1' => ['telephone' => 'abcdefgijk'],
                    ],
                    'email' => [
                        [
                            'email' => null,
                        ],
                    ],
                    'website' => [
                        [
                            'website' => null,
                        ],
                    ],
                    'mailing_address' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($org->user)->put("/activity/$activity->id/contact_info", $postData);

        $validationErrors = Arr::dot(session('errors')->toArray());

        $this->assertContains('The contact info telephone number is invalid.', $validationErrors);
    }

    public function test_contact_info_throws_validation_error_when_number_has_incomplete_parentheses()
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $activity = Activity::create([
            'org_id'          => $org->id,
            'iati_identifier' => [
                'activity_identifier'             => 'SYRZ000041',
                'iati_identifier_text'            => 'CZ-ICO-25755277-SYRZ000041',
                'present_organization_identifier' => 'CZ-ICO-25755277',
            ],
            'title'           => [
                [
                    'narrative' => 'DGGF Track 3',
                    'language'  => 'en',
                ],
            ],
        ]);

        $postData = [
            'contact_info' => [
                [
                    'type' => '1',
                    'organisation' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'department' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'person_name' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'job_title' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'telephone' => [
                        '2' => ['telephone' => '+(977-9841234561'],
                    ],
                    'email' => [
                        [
                            'email' => null,
                        ],
                    ],
                    'website' => [
                        [
                            'website' => null,
                        ],
                    ],
                    'mailing_address' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($org->user)->put("/activity/$activity->id/contact_info", $postData);

        $validationErrors = Arr::dot(session('errors')->toArray());

        $this->assertContains('The contact info telephone number is invalid.', $validationErrors);
    }

    public function test_contact_info_throws_validation_error_when_number_too_long()
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $activity = Activity::create([
            'org_id'          => $org->id,
            'iati_identifier' => [
                'activity_identifier'             => 'SYRZ000041',
                'iati_identifier_text'            => 'CZ-ICO-25755277-SYRZ000041',
                'present_organization_identifier' => 'CZ-ICO-25755277',
            ],
            'title'           => [
                [
                    'narrative' => 'DGGF Track 3',
                    'language'  => 'en',
                ],
            ],
        ]);

        $postData = [
            'contact_info' => [
                [
                    'type' => '1',
                    'organisation' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'department' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'person_name' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'job_title' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'telephone' => [
                        '4' => ['telephone' => '1234567890123456789123456789123456789'],
                    ],
                    'email' => [
                        [
                            'email' => null,
                        ],
                    ],
                    'website' => [
                        [
                            'website' => null,
                        ],
                    ],
                    'mailing_address' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($org->user)->put("/activity/$activity->id/contact_info", $postData);

        $validationErrors = Arr::dot(session('errors')->toArray());
        $this->assertContains(trans('validation.activity_contact_info.telephone.max'), $validationErrors);
    }

    public function test_contact_info_throws_validation_error_when_number_has_invalid_signs()
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();

        $activity = Activity::create([
            'org_id'          => $org->id,
            'iati_identifier' => [
                'activity_identifier'             => 'SYRZ000041',
                'iati_identifier_text'            => 'CZ-ICO-25755277-SYRZ000041',
                'present_organization_identifier' => 'CZ-ICO-25755277',
            ],
            'title'           => [
                [
                    'narrative' => 'DGGF Track 3',
                    'language'  => 'en',
                ],
            ],
        ]);

        $postData = [
            'contact_info' => [
                [
                    'type' => '1',
                    'organisation' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'department' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'person_name' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'job_title' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                    'telephone' => [
                        '4' => ['telephone' => '9860222&123'],
                    ],
                    'email' => [
                        [
                            'email' => null,
                        ],
                    ],
                    'website' => [
                        [
                            'website' => null,
                        ],
                    ],
                    'mailing_address' => [
                        [
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($org->user)->put("/activity/$activity->id/contact_info", $postData);

        $validationErrors = Arr::dot(session('errors')->toArray());
        $this->assertContains(trans('validation.contact_info_telephone_is_invalid'), $validationErrors);
    }
}
