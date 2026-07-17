<?php

namespace App\Services\Subjects;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class SubjectImportSnapshotStore
{
    private function cache(): Repository
    {
        return Cache::store('database');
    }

    private function key(string $token): string
    {
        return "subject-import:snapshot:{$token}";
    }

    public function put(int $userId, string $filename, array $preview): string
    {
        $token = Str::random(64);
        $this->cache()->put($this->key($token), [
            'user_id' => $userId,
            'filename' => basename($filename),
            'created_at' => now()->toJSON(),
            'preview' => $preview,
        ], now()->addMinutes(30));

        return $token;
    }

    public function getForUser(string $token, int $userId): ?array
    {
        $snapshot = $this->cache()->get($this->key($token));

        return is_array($snapshot) && $snapshot['user_id'] === $userId ? $snapshot : null;
    }

    public function claimForUser(string $token, int $userId): ?array
    {
        return $this->cache()->lock("subject-import:lock:{$token}", 10)->block(3, function () use ($token, $userId) {
            $snapshot = $this->getForUser($token, $userId);
            if ($snapshot !== null) {
                $this->cache()->forget($this->key($token));
            }

            return $snapshot;
        });
    }

    public function forget(string $token, int $userId): void
    {
        if ($this->getForUser($token, $userId) !== null) {
            $this->cache()->forget($this->key($token));
        }
    }
}
