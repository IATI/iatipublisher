<?php

declare(strict_types=1);

namespace Tests\Feature\Element;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\User;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\RecipientCountryService;
use App\IATI\Services\Activity\RecipientRegionService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class RecipientCountryCompleteTest.
 */
class RecipientCountryCompleteTest extends ElementCompleteTest
{
    use RefreshDatabase;

    /**
     * Element recipient_country.
     *
     * @var string
     */
    private string $element = 'recipient_country';

    /**
     * @var ActivityService|mixed
     */
    private ActivityService $activityService;

    /**
     * @var RecipientCountryService|mixed
     */
    private RecipientCountryService $recipientCountryService;

    /**
     * @var RecipientRegionService|mixed
     */
    private RecipientRegionService $recipientRegionService;

    /**
     * Setup.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->activityService = app()->make(ActivityService::class);
        $this->recipientCountryService = app()->make(RecipientCountryService::class);
        $this->recipientRegionService = app()->make(RecipientRegionService::class);
    }

    public User $user;

    /**
     * Mandatory attribute test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_recipient_country_mandatory_attributes(): void
    {
        $this->test_mandatory_attributes($this->element, ['country_code']);
    }

    /**
     * Mandatory sub element test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_recipient_country_mandatory_sub_elements(): void
    {
        $this->test_mandatory_sub_elements($this->element, []);
    }

    /**
     * Empty data test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_recipient_country_empty_data(): void
    {
        $recipient_countryData = '';

        $this->test_level_one_multi_dimensional_element_incomplete($this->element, $recipient_countryData);
    }

    /**
     * Empty array test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_recipient_country_empty_array(): void
    {
        $recipient_countryData = json_decode('[]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_one_multi_dimensional_element_incomplete($this->element, $recipient_countryData);
    }

    /**
     * Empty json array test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_recipient_country_empty_json_array(): void
    {
        $recipient_countryData = json_decode('[{}]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_one_multi_dimensional_element_incomplete($this->element, $recipient_countryData);
    }

    /**
     * Attribute country_code empty test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_recipient_country_attribute_empty_country_code(): void
    {
        $recipient_countryData = json_decode('[{"country_code":"","percentage":"100","narrative":[{"narrative":"recipient-country1-narrative1","language":"aa"}]}]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_one_multi_dimensional_element_incomplete($this->element, $recipient_countryData);
    }

    /**
     * Recipient Country element complete test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_recipient_country_element_complete(): void
    {
        $recipient_countryData = json_decode(
            '[{"country_code":"AF","percentage":"100","narrative":[{"narrative":"recipient-country1-narrative1","language":"aa"}]},{"country_code":"AX","percentage":"100","narrative":[{"narrative":"recipient-country2-narrative1","language":"ab"}]}]',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->test_level_one_multi_dimensional_element_complete($this->element, $recipient_countryData);
    }

    /* Percentage based completeness tests */

    /**
     * Ensure RR and RC is complete when RC is 100% with mandatory field filled.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function test_recipient_country_is_complete_and_recipient_region_is_complete_at_100_percentage_with_complete_recipient_country()
    {
        $data = $this->getRecipientCountry(100.0, 'AF');
        $activity = $this->getBasicActivity();
        $this->actingAs($this->user);
        $this->recipientCountryService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertTrue($elementStatus['recipient_country']);
        $this->assertTrue($elementStatus['recipient_region']);
    }

    /**
     * Ensure RR and RC is incomplete when RC is 50% with mandatory field filled.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function test_recipient_country_is_incomplete_and_recipient_region_is_incomplete_at_50_percentage_with_complete_recipient_country()
    {
        $data = $this->getRecipientCountry(50.0, 'AF');
        $activity = $this->getBasicActivity();
        $this->actingAs($this->user);
        $this->recipientCountryService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertFalse($elementStatus['recipient_country']);
        $this->assertFalse($elementStatus['recipient_region']);
    }

    /**
     * Ensure RR and RC is complete when RC is 100% with mandatory field not filled.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function test_recipient_country_is_incomplete_and_recipient_region_is_complete_at_100_percentage_with_incomplete_recipient_country()
    {
        $data = $this->getRecipientCountry(100.0);
        $activity = $this->getBasicActivity();
        $this->actingAs($this->user);
        $this->recipientCountryService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertFalse($elementStatus['recipient_country']);
        $this->assertTrue($elementStatus['recipient_region']);
    }

    /**
     * Ensure RR and RC is incomplete when RC is 50% with mandatory field not filled.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function test_recipient_country_is_incomplete_and_recipient_region_is_incomplete_at_50_percentage_with_incomplete_recipient_country()
    {
        $data = $this->getRecipientCountry(50.0);
        $activity = $this->getBasicActivity();
        $this->actingAs($this->user);
        $this->recipientCountryService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertFalse($elementStatus['recipient_country']);
        $this->assertFalse($elementStatus['recipient_region']);
    }

    /**
     * Test complete status.
     * Scenario: user adds an empty recipient region after initially having added 100% recipient country.
     *
     * @return void
     *
     * @throws BindingResolutionException
     * @throws \JsonException
     */
    public function test_recipient_country_is_complete_and_recipient_region_is_complete_but_incomplete_recipient_region_is_added_at_100_percent_recipient_country()
    {
        $data = $this->getRecipientCountry(100.0, 'AF');
        $activity = $this->getBasicActivity();
        $this->actingAs($this->user);
        $this->recipientCountryService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertTrue($elementStatus['recipient_country']);
        $this->assertTrue($elementStatus['recipient_region']);

        $data = [
            'recipient_region' => [
                [
                    'region_vocabulary' => null,
                    'region_code' => null,
                    'percentage' => null,
                    'narrative' => [
                        [
                            'narrative' => null,
                            'language' => null,
                        ],
                    ],
                ],
            ],
        ];

        $this->recipientRegionService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertTrue($elementStatus['recipient_country']);
        $this->assertFalse($elementStatus['recipient_region']);
    }

    /**
     * Test complete status.
     * Scenario: user adds a Recipient country and multiple Recipient Region that sums upto 100.
     *
     * @return void
     *
     * @throws BindingResolutionException
     * @throws \JsonException
     */
    public function test_recipient_country_is_complete_and_recipient_region_is_complete_when_percentages_sum_up_to_100_where_both_elements_have_mandatory_elements_filled()
    {
        $data = $this->getRecipientCountry(45.5, 'AF');
        $activity = $this->getBasicActivity();
        $this->actingAs($this->user);
        $this->recipientCountryService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertFalse($elementStatus['recipient_country']);
        $this->assertFalse($elementStatus['recipient_region']);

        $data = [
            'recipient_region' => [
                    [
                        'region_vocabulary' => '1',
                        'region_code' => '88',
                        'percentage' => '20',
                        'narrative' => [
                            [
                                'narrative' => null,
                                'language' => null,
                            ],
                        ],
                    ],
                    [
                        'region_vocabulary' => '2',
                        'custom_code' => '123',
                        'percentage' => '54.5',
                        'narrative' => [
                            [
                                'narrative' => null,
                                'language' => null,
                            ],
                        ],
                    ],
                    [
                        'region_vocabulary' => '1',
                        'region_code' => '289',
                        'percentage' => '34.5',
                        'narrative' => [
                            [
                                'narrative' => null,
                                'language' => null,
                            ],
                        ],
                    ],
                ],
            'total_region_percentage'=>'54.5',
        ];

        $this->recipientRegionService->update($activity->id, $data);
        $activity = $this->activityService->getActivity($activity->id);
        $elementStatus = $activity->element_status;

        $this->assertTrue($elementStatus['recipient_country']);
        $this->assertTrue($elementStatus['recipient_region']);
    }

    /**
     * Returns activity.
     *
     * @return Activity
     */
    private function getBasicActivity(): Activity
    {
        $org = Organization::factory()->create();
        $user = User::factory(['organization_id'=>$org->id])->create();
        $this->user = $user;

        return Activity::create([
            'iati_identifier' => [
                'activity_identifier' => 'test-activity',
            ],
            'activity_identifier' => 'test-activity',
            'other_identifier' => [
                [
                    'reference' => 'other-identifier-reference',
                    'reference_type' => 'A1',
                    'owner_org' => [
                        [
                            'ref' => 'owner-org-reference',
                            'narrative' => [
                                [
                                    'narrative' => 'owner-org-narrative',
                                    'language' => 'ab',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'title' => [
                [
                    'narrative' => 'Test activityy',
                    'language' => 'en',
                ],
            ],
            'description' => [
                [
                    'type' => '1',
                    'narrative' => [
                        [
                            'narrative' => 'description-narrative',
                            'language' => 'en',
                        ],
                    ],
                ],
            ],
            'activity_status' => 1,
            'status' => 'draft',
            'activity_date' => [
                [
                    'type' => '2',
                    'date' => '2022-01-02',
                    'narrative' => [
                        [
                            'narrative' => 'activity-date-narrative',
                            'language' => 'en',
                        ],
                    ],
                ],
            ],
            'contact_info' => null,
            'activity_scope' => 1,
            'participating_org' => null,
            'recipient_country' => null,
            'recipient_region' => null,
            'location' => null,
            'sector' => [
                [
                    'sector_vocabulary' => '1',
                    'code' => '11120',
                    'percentage' => '100',
                    'narrative' => [
                        [
                            'narrative' => 'sector-narrative',
                            'language' => 'en',
                        ],
                    ],
                ],
            ],
            'country_budget_items' => null,
            'humanitarian_scope' => null,
            'policy_marker' => null,
            'collaboration_type' => null,
            'default_flow_type' => null,
            'default_finance_type' => null,
            'default_aid_type' => null,
            'default_tied_status' => null,
            'budget' => null,
            'planned_disbursement' => null,
            'capital_spend' => null,
            'document_link' => null,
            'related_activity' => null,
            'legacy_data' => null,
            'conditions' => null,
            'org_id' => $org->id,
            'default_field_values' => [
                'default_currency' => 'GBP',
                'default_language' => 'en',
                'hierarchy' => '2',
                'budget_not_provided' => '',
                'humanitarian' => '0',
            ],
            'linked_to_iati' => false,
            'tag' => null,
            'element_status' => [
                'iati_identifier' => true,
                'title' => true,
                'description' => true,
                'activity_status' => true,
                'activity_date' => true,
                'activity_scope' => true,
                'recipient_country' => false,
                'recipient_region' => false,
                'collaboration_type' => false,
                'default_flow_type' => false,
                'default_finance_type' => false,
                'default_aid_type' => false,
                'default_tied_status' => false,
                'capital_spend' => false,
                'related_activity' => false,
                'conditions' => false,
                'sector' => true,
                'humanitarian_scope' => false,
                'legacy_data' => false,
                'tag' => false,
                'policy_marker' => false,
                'other_identifier' => true,
                'country_budget_items' => false,
                'budget' => false,
                'participating_org' => false,
                'document_link' => false,
                'contact_info' => false,
                'location' => false,
                'planned_disbursement' => false,
                'transactions' => false,
                'result' => false,
                'reporting_org' => true,
            ],
            'created_at' => '2023-06-06T04:16:19.000000Z',
            'updated_at' => '2023-06-06T04:19:14.000000Z',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'reporting_org' => [
                [
                    'ref' => 'GB-COH-4905100',
                    'type' => '21',
                    'secondary_reporter' => '',
                    'narrative' => [
                        [
                            'narrative' => 'Resource Extraction Monitoring',
                            'language' => 'en',
                        ],
                    ],
                ],
            ],
            'upload_medium' => 'manual',
            'migrated_from_aidstream' => false,
            'complete_percentage' => 56.25,
        ]);
    }

    /**
     * Returns Recipient Country of size 1.
     *
     * @param $percentage
     * @param null $countryCode
     * @return array
     */
    private function getRecipientCountry($percentage, $countryCode = null): array
    {
        return [
            'recipient_country'=>[
                [
                    'country_code' => $countryCode,
                    'percentage' => (float) $percentage,
                    'narrative' => [
                        [
                            'narrative' => 'recipient-country-narrative',
                            'language' => 'en',
                        ],
                    ],
                ],
            ],
            'total_country_percentage'=>(float) $percentage,
        ];
    }
}
