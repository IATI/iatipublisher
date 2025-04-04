<?php

namespace Tests\Feature\Element;

/**
 * Class RelatedActivityCompleteTest.
 */
class RelatedActivityCompleteTest extends ElementCompleteTest
{
    /**
     * Element related_activity.
     *
     * @var string
     */
    private string $element = 'related_activity';

    /**
     * Mandatory attribute test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_related_activity_mandatory_attributes(): void
    {
        $this->test_mandatory_attributes($this->element, ['activity_identifier', 'relationship_type']);
    }

    /**
     * Mandatory sub element test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_related_activity_mandatory_sub_elements(): void
    {
        $this->test_mandatory_sub_elements($this->element, []);
    }

    /**
     * All element empty test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_related_activity_all_element_empty(): void
    {
        $related_activityData = json_decode('[{"activity_identifier":"","relationship_type":""}]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_one_multi_dimensional_element_incomplete($this->element, $related_activityData);
    }

    /**
     * Attribute activity_identifier empty test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_related_activity_attribute_empty_activity_identifier(): void
    {
        $related_activityData = json_decode('[{"activity_identifier":"","relationship_type":"123"}]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_one_multi_dimensional_element_incomplete($this->element, $related_activityData);
    }

    /**
     * Attribute relationship_type empty test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_related_activity_attribute_empty_relationship_type(): void
    {
        $related_activityData = json_decode('[{"activity_identifier":"123","relationship_type":""}]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_one_multi_dimensional_element_incomplete($this->element, $related_activityData);
    }

    /**
     * Related Activity element complete test.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_related_activity_element_complete(): void
    {
        $related_activityData = json_decode('[{"activity_identifier":"DK-CVR-31378028-SPA-2020-edited","relationship_type":"1"}]', true, 512, JSON_THROW_ON_ERROR);

        $this->test_level_one_multi_dimensional_element_complete($this->element, $related_activityData);
    }
}
