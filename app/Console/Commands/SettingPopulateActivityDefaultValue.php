<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class SettingPopulateActivityDefaultValue.
 */
class SettingPopulateActivityDefaultValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:activity-default-value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates settings table activity default value column with new keys';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('settings')->chunkById(10, function ($settings) {
            foreach ($settings as $setting) {
                $activityDefaultValue = json_decode($setting->activity_default_values, true) ?? [];
                $newFields = [
                      'linked_data_uri' => '',
                      'default_collaboration_type' => '',
                      'default_flow_type' => '',
                      'default_finance_type' => '',
                      'default_aid_type' => '',
                      'default_tied_status' => '',
                      'hierarchy' => $activityDefaultValue['hierarchy'] ?? '',
                      'humanitarian' => $activityDefaultValue['humanitarian'] ?? '',
                      'budget_not_provided' => $activityDefaultValue['budget_not_provided'] ?? '',
                ];
                DB::table('settings')->where('id', $setting->id)->update(['activity_default_values' => json_encode($newFields)]);
            }
        });
    }
}
