<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class AuditLog
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'remember_token',
        'current_password',
        'new_password',
    ];

    public static function record(
        string $logName,
        string $description,
        array $properties = [],
        ?Model $subject = null,
        ?Model $causer = null,
    ): void {
        $logger = activity()
            ->useLog($logName)
            ->withProperties(self::sanitize($properties));

        if ($causer) {
            $logger->causedBy($causer);
        }

        if ($subject) {
            $logger->performedOn($subject);
        }

        $logger->log($description);
    }

    private static function sanitize(array $properties): array
    {
        return collect($properties)
            ->reject(fn ($value, $key) => in_array((string) $key, self::SENSITIVE_KEYS, true))
            ->map(function ($value) {
                if (is_array($value)) {
                    return self::sanitize($value);
                }

                return $value;
            })
            ->all();
    }
}
