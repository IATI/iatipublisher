<?php

namespace Database\Factories\IATI\Models\Activity;

use App\IATI\Models\Activity\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Model>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'iati_identifier' => [
                'activity_identifier'  => 'SYRZ000041',
                'iati_identifier_text' => 'CZ-ICO-25755277-SYRZ000041',
                'present_organization_identifier' => 'CZ-ICO-25755277',
            ],
            'title'           => [
                [
                    'narrative' => 'DGGF Track 3',
                    'language'  => 'en',
                ],
            ],
            'activity_identifier'=>'SYRZ000041',
        ];
    }
}
