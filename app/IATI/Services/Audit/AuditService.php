<?php

declare(strict_types=1);

namespace App\IATI\Services\Audit;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\User;
use App\IATI\Repositories\Audit\AuditRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * Class ActivityService.
 */
class AuditService
{
    /**
     * @var AuditRepository
     */
    protected AuditRepository $auditRepository;

    /**
     * @var ?int
     */
    protected ?int $auditableId = null;

    /**
     * AuditService constructor.
     *
     * @param AuditRepository $auditRepository
     */
    public function __construct(AuditRepository $auditRepository)
    {
        $this->auditRepository = $auditRepository;
    }

    /**
     * Stores custom event not caught by laravel-audit package in the audit table.
     *
     * @param mixed $auditables
     * @param string $event
     * @param string $eventFormat
     *
     * @return void
     */
    public function auditEvent(object|array|string|null $auditables, string $event, string $eventFormat = ''): void
    {
        $event = $this->buildEvent($event, $eventFormat);
        $this->auditRepository->insertRows($this->populateColumn($auditables, $event));
    }

    /**
     * Populate a columns in a row.
     *
     * @param mixed $auditables
     * @param string $event
     *
     * @return array
     */
    public function populateColumn(mixed $auditables, string $event): array
    {
        $row = [];
        $row['user_type'] = $this->getUserType();
        $row['user_id'] = $this->getUserId($event);
        $row['auditable_type'] = $event === 'signin' ? 'App\\IATI\\Models\\User\\User' : $this->getAuditableType($auditables);
        $row['auditable_id'] = $this->getAuditableId($event);
        $row['event'] = $event;
        $row['url'] = request()->getUri();
        $row['ip_address'] = request()->getClientIp();
        $row['user_agent'] = request()->userAgent();
        $row['created_at'] = now();
        $row['updated_at'] = now();
        $row['new_values'] = $this->getNewValues($auditables, $row['auditable_type'], $event);
        $row['full_name'] = auth()->user()->full_name;
        $row['email'] = auth()->user()->email;

        return $row;
    }

    /**
     * Builds the complete event.
     * example: download-csv, download-xml...
     *
     * @param string $event
     * @param string $eventFormat
     *
     * @return string
     */
    public function buildEvent(string $event, string $eventFormat): string
    {
        if ($event === 'download') {
            $event = empty($eventFormat) ? $event : $event . '-' . $eventFormat;
        }

        return $event;
    }

    /**
     * Returns user_type.
     * example: App\IATI\Models\User\User.
     *
     * @return string
     */
    public function getUserType(): string
    {
        return 'App\\IATI\\Models\\User\\User';
    }

    /**
     * Return user_id.
     * example: 2, 3, 4...
     * 1 in-case of migration event.
     *
     * @param string $event
     * @return int|string
     */
    public function getUserId(string $event = ''): int | string
    {
        if ($event && str_contains($event, 'migrated')) {
            return 1;
        }

        return Auth::id();
    }

    /**
     * Returns paginated audits.
     *
     * @param int $page
     * @param array $queryParams
     *
     * @return LengthAwarePaginator
     */
    public function getAuditLog(int $page, array $queryParams): LengthAwarePaginator
    {
        return $this->auditRepository->getAuditLog($page, $queryParams);
    }

    /**
     * Updates audit data row.
     *
     * @param $id
     * @param $data
     *
     * @return void
     */
    public function update($id, $data): void
    {
        $this->auditRepository->update($id, $data);
    }

    /**
     * Return auditable_type.
     * example: App\IATI\Models\User\User, App\IATI\Models\Activity\Activity...
     *
     * @param mixed $auditables
     *
     * @return string|null
     */
    public function getAuditableType(object|array|string|null $auditables): string|null
    {
        if (is_string($auditables)) {
            return $auditables;
        } elseif (is_array($auditables) || is_object($auditables)) {
            return get_class($auditables[0]);
        } else {
            return null;
        }
    }

    /**
     * Returns encrypted values if user models custom event is audited.
     *
     * @param $auditables
     * @param $auditableType
     * @param $event
     *
     *
     * @return bool|string
     */
    public function getNewValues($auditables, $auditableType, $event): bool | string
    {
        $auditables = $auditables->toArray();

        if ($auditableType === 'App\\IATI\\Models\\User\\User' && $event !== 'signin') {
            foreach ($auditables as $index => $item) {
                foreach ($item as $key => $value) {
                    $item[$key] = Crypt::encryptString($value);
                }
                $auditables[$index] = $item;
            }
        }

        if ($auditableType === 'App\\IATI\\Models\\User\\User' && $event === 'signin') {
            foreach ($auditables as $index => $item) {
                if ($index === 'id') {
                    $auditables[$index] = Crypt::encryptString($item);
                } else {
                    unset($auditables[$index]);
                }
            }
        }

        return json_encode($auditables);
    }

    /**
     * Returns auditable id.
     *
     * @param $event
     *
     * @return ?int
     */
    public function getAuditableId($event): ?int
    {
        switch ($event) {
            case 'signin':
                return $this->getUserId();
            case str_contains($event, 'migrated'):
                return  $this->auditableId;
            default:
                return null;
        }
    }

    /**
     * Sets auditable id.
     *
     * @param int $auditableId
     *
     * @return $this
     */
    public function setAuditableId(int $auditableId): static
    {
        $this->auditableId = $auditableId;

        return $this;
    }

    /**
     * Audit migration event.
     *
     * @param $element
     * @param $event
     *
     * @return AuditService
     */
    public function auditMigrationEvent($element, $event): static
    {
        $row = [];
        $row['user_type'] = $this->getUserType();
        $row['user_id'] = $this->getUserId($event);
        $row['auditable_type'] = get_class($element);
        $row['auditable_id'] = $this->getAuditableId($event);
        $row['event'] = $event;
        $row['url'] = request()->getUri();
        $row['ip_address'] = request()->getClientIp();
        $row['user_agent'] = request()->userAgent();
        $row['created_at'] = now();
        $row['updated_at'] = now();
        $row['new_values'] = $this->encryptAuditableItemIfUserOrOrganization($element->toArray(), $row['auditable_type']);
        $row['full_name'] = auth()->user() ? auth()->user()->full_name : null;
        $row['email'] = auth()->user() ? auth()->user()->email : null;

        $this->auditRepository->insertRows($row);

        return  $this;
    }

    /**
     * Returns plain text auditableItem or encrypted if auditableItem is User or Org.
     *
     * @param array $auditableItem
     * @param string $auditableType
     *
     * @return string|bool
     */
    public function encryptAuditableItemIfUserOrOrganization(array $auditableItem, string $auditableType): string|bool
    {
        if ($auditableType === get_class(new User) || $auditableType === get_class(new Organization)) {
            $auditableItem = $this->recursivelyEncrypt($auditableItem);
        }

        return json_encode($auditableItem);
    }

    /**
     * Recursively Encrypt an array.
     *
     * @param $item
     *
     * @return mixed
     */
    private function recursivelyEncrypt($item): mixed
    {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                $item[$key] = $this->recursivelyEncrypt($value);
            }
        } elseif (is_string($item)) {
            $item = Crypt::encryptString($item);
        }

        return $item;
    }
}
