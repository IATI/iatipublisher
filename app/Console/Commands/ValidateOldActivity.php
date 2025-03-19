<?php

namespace App\Console\Commands;

use App\IATI\Services\Workflow\BulkPublishingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ValidateOldActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate:activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $activityId = $this->askValid(
            'Please enter the activity ids which you want to validate separated by comma (Compulsory)',
            'activityId',
            ['required']
        );

//       $activityId = [];
//       DB::table('iati_validator_responses')
//           ->whereJsonDoesntContainKey('response->errors->0->details')
//           ->chunkById(10, function($validators) use (&$activityId) {
//              foreach($validators as $validator)
//              {
//                  $response = json_decode($validator->response,true);
//
//                  if((isset($response['errors']) && empty($response['errors'])) || isset($response['errors'][0]['details']))
//                  {
//                      continue;
//                  }
//
//                  $activityId[] = $validator->activity_id;
//              }
//           });
        $activityId = explode(',', $activityId);
        $activityWorkFlowController = app()->make(BulkPublishingService::class);
        $activityWorkFlowController->validateActivitiesOnIATI($activityId);
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
}
