<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\IATI\Models\Activity\Activity;
use App\IATI\Services\ElementCompleteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class CompleteSpecificOrganizationActivites.
 */
class CompleteSpecificOrganizationActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complete:specific-organization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will ensure that all activities for a specific organization are complete if applicable.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();
            $organizationIdString = $this->askValid(
                'Please enter the organization ids which you want make activity status complete separated by comma (Compulsory)',
                'organizationId',
                ['required']
            );

            $organizationIds = explode(',', $organizationIdString);

            Activity::query()->whereIn('org_id', $organizationIds)->chunkById(100, function ($activities) {
                /** @var ElementCompleteService $elementCompleteService */
                $elementCompleteService = app(ElementCompleteService::class);

                foreach ($activities as $activity) {
                    $elementCompleteService->refreshElementStatus($activity);
                    $this->info('Activity Id: ' . $activity->id . ' completed.');
                }
            });
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->error($exception->getMessage());
        }
    }

    /**
     * Ask input from user and return value.
     *
     * @param $question
     * @param $field
     * @param $rules
     *
     * @return string
     */
    protected function askValid($question, $field, $rules): string
    {
        $value = $this->ask($question);
        $message = $this->validateInput($rules, $field, $value);

        if ($message) {
            $this->error($message);

            return $this->askValid($question, $field, $rules);
        }

        return $value;
    }

    /**
     * Validates input given by user.
     *
     * @param $rules
     * @param $fieldName
     * @param $value
     *
     * @return string|null
     */
    protected function validateInput($rules, $fieldName, $value): ?string
    {
        $validator = Validator::make([
            $fieldName => $value,
        ], [
            $fieldName => $rules,
        ]);

        return $validator->fails()
            ? $validator->errors()->first($fieldName)
            : null;
    }
}
