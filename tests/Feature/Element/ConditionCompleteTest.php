<?php

declare(strict_types=1);

namespace Tests\Feature\Element;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use App\IATI\Services\ElementCompleteService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ConditionCompleteTest.
 */
class ConditionCompleteTest extends ElementCompleteTest
{
    use RefreshDatabase;
    /**
     * Element conditions.
     *
     * @var string
     */
    private string $element = 'conditions';

    /**
     * Mandatory attribute test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_mandatory_attributes(): void
    {
        $this->test_mandatory_attributes($this->element, ['condition_attached']);
    }

    /**
     * Mandatory sub element test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_mandatory_sub_elements(): void
    {
        $this->test_mandatory_sub_elements($this->element, ['condition' => ['condition_type']]);
    }

    /**
     * Empty condition test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_empty_data(): void
    {
        $humanitarian_scopeData = '';

        $this->test_level_two_single_dimensional_element_incomplete($this->element, $humanitarian_scopeData);
    }

    /**
     * Empty condition array test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_empty_array(): void
    {
        $humanitarian_scopeData = json_decode('[]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_two_single_dimensional_element_incomplete($this->element, $humanitarian_scopeData);
    }

    /**
     * Attribute condition_attached empty test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_attribute_empty_condition_attached(): void
    {
        $humanitarian_scopeData = json_decode('{"condition_attached":"","condition":[{"condition_type":"1","narrative":[{"narrative":"asdads","language":"ab"}]}]}', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_two_single_dimensional_element_incomplete($this->element, $humanitarian_scopeData);
    }

    /**
     * Sub element condition empty array test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_sub_element_empty_condition_array(): void
    {
        $humanitarian_scopeData = json_decode('{"condition_attached":"1","condition":[]}', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_two_single_dimensional_element_incomplete($this->element, $humanitarian_scopeData);
    }

    /**
     * Sub element condition attribute condition_type empty test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_sub_element_condition_attribute_empty_condition_type(): void
    {
        $humanitarian_scopeData = json_decode('{"condition_attached":"1","condition":[{"condition_type":"","narrative":[{"narrative":"asdads","language":"ab"}]}]}', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_two_single_dimensional_element_incomplete($this->element, $humanitarian_scopeData);
    }

    /**
     * Sub element condition empty json array test.
     *
     * @return void
     *
     * @throws BindingResolutionException
     * @throws \JsonException
     */
    public function test_condition_when_it_is_saved_with_condition_attached_0_and_remaining_is_empty()
    {
        $role = Role::factory()->create();
        $org = Organization::factory()->has(User::factory(['role_id' => $role->id]))->create();
        $activity = Activity::factory()->create([
            'org_id' => $org->id,
            'created_by' => $org->user->id,
            'updated_by' => $org->user->id,
        ]);
        $conditions = json_decode('{"condition_attached":"0","condition":[{}]}', true, 512, JSON_THROW_ON_ERROR);
        $activity->conditions = $conditions;

        $elementCompleteService = app()->make(ElementCompleteService::class);
        $this->assertTrue($elementCompleteService->isConditionsElementCompleted($activity));
    }

    /**
     * Test completeness when condition_attached is 0 and all sub-elements are filled.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_when_it_is_saved_with_condition_attached_0_and_remaining_is_filled()
    {
        $data = json_decode('{"condition_attached":"0","condition":[{"condition_type":"1","narrative":[{"narrative":"asdads","language":"ab"}]},{"condition_type":"2","narrative":[{"narrative":"asdasdadad","language":"aa"}]}]}', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_two_single_dimensional_element_complete($this->element, $data);
    }

    /**
     * Sub element condition empty json array test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_when_it_is_saved_with_condition_attached_1_and_remaining_is_empty()
    {
        $data = json_decode('{"condition_attached":"1","condition":[{}]}', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_two_single_dimensional_element_incomplete($this->element, $data);
    }

    /**
     * Test condition is incomplete when saving all null.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_when_it_is_saved_without_selecting_condition_attached()
    {
        $data = json_decode('{"condition_attached":null,"condition":[{"condition_type":null,"narrative":[{"narrative":null,"language":null}]}]}', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_two_single_dimensional_element_incomplete($this->element, $data);
    }

    /**
     * Condition element complete test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_condition_element_complete(): void
    {
        $actualData = json_decode(
            '{"condition_attached":"1","condition":[{"condition_type":"1","narrative":[{"narrative":"asdads","language":"ab"}]},{"condition_type":"2","narrative":[{"narrative":"asdasdadad","language":"aa"}]}]}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->test_level_two_single_dimensional_element_complete($this->element, $actualData);
    }
}
