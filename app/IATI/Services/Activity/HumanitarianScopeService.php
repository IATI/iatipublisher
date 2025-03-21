<?php

declare(strict_types=1);

namespace App\IATI\Services\Activity;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Models\Activity\Activity;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Traits\XmlBaseElement;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;

/**
 * Class HumanitarianScopeService.
 */
class HumanitarianScopeService
{
    use XmlBaseElement;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepository;

    /**
     * @var ParentCollectionFormCreator
     */
    protected ParentCollectionFormCreator $parentCollectionFormCreator;

    /**
     * HumanitarianScopeService constructor.
     *
     * @param ActivityRepository          $activityRepository
     * @param ParentCollectionFormCreator $parentCollectionFormCreator
     */
    public function __construct(ActivityRepository $activityRepository, ParentCollectionFormCreator $parentCollectionFormCreator)
    {
        $this->activityRepository = $activityRepository;
        $this->parentCollectionFormCreator = $parentCollectionFormCreator;
    }

    /**
     * Returns humanitarian scope data of an activity.
     *
     * @param int $activity_id
     *
     * @return array|null
     */
    public function getHumanitarianScopeData(int $activity_id): ?array
    {
        return $this->activityRepository->find($activity_id)->humanitarian_scope;
    }

    /**
     * Returns activity object.
     *
     * @param $id
     *
     * @return object
     */
    public function getActivityData($id): object
    {
        return $this->activityRepository->find($id);
    }

    /**
     * Updates activity humanitarian scope.
     *
     * @param $id
     * @param $activityHumanitarianScope
     *
     * @return bool
     */
    public function update($id, $activityHumanitarianScope): bool
    {
        $activityHumanitarianScope = $this->sanitizeHumanitarianScopeData($activityHumanitarianScope);

        $activity = $this->activityRepository->find($id);
        $deprecationStatusMap = $activity->deprecation_status_map;
        $deprecationStatusMap['humanitarian_scope'] = doesHumanitarianScopeHaveDeprecatedCode($activityHumanitarianScope);

        return $this->activityRepository->update($activity->id, [
            'humanitarian_scope'     => $activityHumanitarianScope,
            'deprecation_status_map' => $deprecationStatusMap,
        ]);
    }

    /**
     * Generates humanitarian scope form.
     *
     * @param $id
     *
     * @return Form
     * @throws \JsonException
     */
    public function formGenerator($id, $activityDefaultFieldValues, $deprecationStatusMap = []): Form
    {
        $element = getElementSchema('humanitarian_scope');
        $model['humanitarian_scope'] = $this->getHumanitarianScopeData($id);
        $this->parentCollectionFormCreator->url = route('admin.activity.humanitarian-scope.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element, 'PUT', '/activity/' . $id, $activityDefaultFieldValues, deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * Returns data in required xml array format.
     *
     * @param Activity $activity
     *
     * @return array
     */
    public function getXmlData(Activity $activity): array
    {
        $activityHumanitarianScope = [];
        $humanitarianScopes = (array) $activity->humanitarian_scope;

        foreach ($humanitarianScopes as $humanitarianScope) {
            $activityHumanitarianScope[] = [
                '@attributes' => [
                    'type'           => Arr::get($humanitarianScope, 'type', null),
                    'vocabulary'     => Arr::get($humanitarianScope, 'vocabulary', null),
                    'vocabulary-uri' => Arr::get($humanitarianScope, 'vocabulary_uri', null),
                    'code'           => Arr::get($humanitarianScope, 'code', null),
                ],
                'narrative'   => $this->buildNarrative(Arr::get($humanitarianScope, 'narrative', null), ),
            ];
        }

        return $activityHumanitarianScope;
    }

    /**
     * Sanitizes humanitarian scope data.
     *
     * @param $activityHumanitarianScope
     *
     * @return array
     */
    public function sanitizeHumanitarianScopeData($activityHumanitarianScope): array
    {
        foreach ($activityHumanitarianScope['humanitarian_scope'] as $key => $humanitarian_scope) {
            $activityHumanitarianScope['humanitarian_scope'][$key]['narrative'] = array_values($humanitarian_scope['narrative']);
        }

        return array_values($activityHumanitarianScope['humanitarian_scope']);
    }
}
