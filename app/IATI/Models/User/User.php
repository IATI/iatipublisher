<?php

declare(strict_types=1);

namespace App\IATI\Models\User;

use App\IATI\Models\Organization\Organization;
use App\Mail\NewUserEmail;
use Carbon\Carbon;
use Database\Factories\IATI\Models\User\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class User.
 */
class User extends Authenticatable implements MustVerifyEmail, Auditable
{
    use HasApiTokens, HasFactory, Notifiable, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'full_name',
        'email',
        'address',
        'organization_id',
        'is_active',
        'email_verified_at',
        'password',
        'role_id',
        'status',
        'language_preference',
        'registration',
        'migrated_from_aidstream',
        'created_at',
        'updated_at',
        'registration_method',
        'last_logged_in',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Before inbuilt function.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(
            function ($model) {
                if (Auth::check()) {
                    $model->created_by = auth()->user()->id;
                    $model->updated_by = auth()->user()->id;
                }
            }
        );

        static::updating(
            function ($model) {
                if (Auth::check()) {
                    $model->updated_by = auth()->user()->id;
                }
            }
        );
    }

    /**
     * Checks if email is verified.
     *
     * @param $email_verified_at
     *
     * @return bool
     */
    public function isEmailVerified($email_verified_at): bool
    {
        return (bool) $email_verified_at;
    }

    /**
     * @return UserFactory
     */
    public static function newFactory(): UserFactory
    {
        return new UserFactory();
    }

    /**
     * @return BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Sends verification email to new user.
     */
    public static function sendEmail(): void
    {
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject(trans('common/common.verify_email_address'))
                ->greeting(trans('common/common.hello') . ' ' . $notifiable->full_name)
                ->line(trans('common/common.welcome_to_iati_publisher_your_email_has_been_used_to_create_a_new_account_here_please_click_the_button_below_to_verify_that_you_have_created_the_account_in_it'))
                ->action(trans('common/common.verify_email_address'), $url);
        });
    }

    /**
     * Sends password reset email to new user.
     *
     * @param $user
     */
    public static function sendNewUserEmail($user): void
    {
        $mailDetails = [
            'greeting' =>trans('common/common.hello') . ' ' . $user->username,
            'message' => trans('common/common.welcome_to_iati_publisher_your_email_has_been_used_to_create_a_new_account_here_please_click_the_button_below_to_update_the_password_of_your_account'),
            'password_update' => true,
            'token' => app('auth.password.broker')->createToken($user),
        ];

        Mail::to($user->email)->send(new NewUserEmail($user, $mailDetails));
    }

    /**
     * Sends verification email to new user.
     *
     * @param $user
     */
    public static function resendEmail($user): void
    {
        $user->sendEmailVerificationNotification();
    }

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo('App\IATI\Models\User\Role', 'role_id');
    }

    /**
     * Encrypt when user model is audited.
     *
     * {@inheritdoc}
     */
    public function transformAudit(array $data): array
    {
        if ($data['old_values']) {
            foreach ($data['old_values'] as $key => $val) {
                $data['old_values'][$key] = Crypt::encryptString($val);
            }
        }

        if ($data['new_values']) {
            foreach ($data['new_values'] as $key => $val) {
                $data['new_values'][$key] = Crypt::encryptString($val);
            }
        }

        return $data;
    }

    /**
     * Returns users registered today.
     *
     * @param $query
     *
     * @return Builder
     */
    public function scopeRegisteredToday($query): Builder
    {
        return $query->whereDate('created_at', Carbon::today());
    }
}
