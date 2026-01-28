<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserDateRangeApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array[]
     */
    public array $testDates = [];

    /**
     * @var string
     */
    public string $baseUrl = 'dashboard/user?';

    /**
     * @var
     */
    public $superAdmin;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testDates = [
            'today' => ['start_date' => Carbon::now()->startOfDay()->format('Y-m-d'), 'end_date' => Carbon::now()->endOfDay()->format('Y-m-d')],
            'this_week' => ['start_date' => Carbon::now()->startOfWeek(), 'end_date' => Carbon::now()->endOfWeek()],
            'this_month' => ['start_date' => Carbon::now()->startOfMonth(), 'end_date' => Carbon::now()->endOfMonth()],
            'this_year' => ['start_date' => Carbon::now()->startOfYear(), 'end_date' => Carbon::now()->endOfYear()],
            'last_7_days' => ['start_date' => Carbon::now()->subDays(6)->startOfDay(), 'end_date' => Carbon::now()->endOfDay()],
            'last_6_months' => ['start_date' => Carbon::now()->subMonths(5)->startOfDay(), 'end_date' => Carbon::now()->endOfDay()],
            'last_12_months' => ['start_date' => Carbon::now()->subMonths(11)->startOfDay(), 'end_date' => Carbon::now()->endOfDay()],
            'all_time' => ['start_date' => Carbon::createFromDate('2010', '01', '01'), 'end_date' => Carbon::now()->endOfDay()],
        ];

        $startDate = now()->subYear()->subDays(2);
        $endDate = now();
        $interval = $startDate->diffInDays($endDate);

        Artisan::call('db:seed', ['--class' => 'RoleTableSeeder']);
        $roleId = Role::where('role', 'admin')->first()->id;
        $superadminRoleId = Role::where('role', 'superadmin')->first()->id;

        for ($i = 0; $i <= $interval; $i++) {
            User::create([
                'username' => $this->getRandomUsername(),
                'email' => $this->getRandomUsername() . '@example.com',
                'password' => Hash::make('password'),
                'full_name' => 'Young Innovations',
                'address' => 'Mahalaxmisthan, Lalitpur',
                'status' => true,
                'role_id' => $roleId,
                'created_at' => $startDate->copy()->addDays($i),
            ]);
        }

        $this->superAdmin = User::create([
            'username' => $this->getRandomUsername(),
            'email' => $this->getRandomUsername() . '@example.com',
            'password' => Hash::make('password'),
            'full_name' => 'Young Innovations',
            'address' => 'Mahalaxmisthan, Lalitpur',
            'status' => true,
            'role_id' => $superadminRoleId,
        ]);
    }

    /**
     * Test response 200 for dashboard/user/stats.
     *
     * @return void
     */
    public function test_user_stats_api()
    {
        $url = 'dashboard/user/stats';
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user/download.
     *
     * @return void
     */
    public function test_user_download_api()
    {
        $url = 'dashboard/user/download';
        $this->actingAs($this->superAdmin)->get(url($url))->assertDownload();
    }

    /**
     * Test response 200 for dashboard/user/count.
     *
     * @return void
     */
    public function test_user_count_api()
    {
        $url = 'dashboard/user/count';
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for today.
     *
     * @return void
     */
    public function test_user_date_range_api_today()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'today.start_date') . '&end_date=' . Arr::get($this->testDates, 'today.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for this week.
     *
     * @return void
     */
    public function test_user_date_range_api_this_week()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'this_week.start_date') . '&end_date=' . Arr::get($this->testDates, 'this_week.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for last 7 days.
     *
     * @return void
     */
    public function test_user_date_range_api_last_7_days()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'last_7_days.start_date') . '&end_date=' . Arr::get($this->testDates, 'last_7_days.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for this month.
     *
     * @return void
     */
    public function test_user_date_range_api_this_month()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'this_month.start_date') . '&end_date=' . Arr::get($this->testDates, 'this_month.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for last 6 month.
     *
     * @return void
     */
    public function test_user_date_range_api_last_6_months()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'last_6_months.start_date') . '&end_date=' . Arr::get($this->testDates, 'last_6_months.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for this year.
     *
     * @return void
     */
    public function test_user_date_range_api_this_year()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'this_year.start_date') . '&end_date=' . Arr::get($this->testDates, 'this_year.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for last 12 month.
     *
     * @return void
     */
    public function test_user_date_range_api_last_12_months()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'last_12_months.start_date') . '&end_date=' . Arr::get($this->testDates, 'last_12_months.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Test response 200 for dashboard/user?... date-range for all time.
     *
     * @return void
     */
    public function test_user_date_range_api_all_time()
    {
        $url = url($this->baseUrl . 'start_date=' . Arr::get($this->testDates, 'all_time.start_date') . '&end_date=' . Arr::get($this->testDates, 'all_time.end_date'));
        $this->actingAs($this->superAdmin)->get(url($url))->assertStatus(200);
    }

    /**
     * Returns random string.
     *
     * @return string
     */
    private function getRandomUsername(): string
    {
        return Str::random(10) . '_' . rand(1, 100000);
    }

    /**
     * @param mixed $case
     * @return int|void
     */
    public function getExpectedCaseOfCount(mixed $case)
    {
        return match ($case) {
            'today' => 1,
            'last_7_days' => 7,
            'last_6_months' => 6,
            'last_12_months' => 12,
            'this_week', 'this_month' => Arr::get($this->testDates, "$case.end_date")->diffInDays(Arr::get($this->testDates, "$case.start_date")) + 1,
            default => Arr::get($this->testDates, "$case.end_date")->diffInMonths(Arr::get($this->testDates, "$case.start_date")) + 1,
        };
    }
}
