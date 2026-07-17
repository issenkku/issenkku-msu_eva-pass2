<?php

namespace App\Services\Subjects;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class SubjectImportResultStore
{
    private function cache(): Repository
    {
        return Cache::store('database');
    }

    private function key(string $token): string
    {
        return "subject-import:result:{$token}";
    }

    public function put(int $userId, array $result): string
    {
        $token = Str::random(64);
        $this->cache()->put($this->key($token), ['user_id' => $userId, 'result' => $result], now()->addMinutes(10));

        return $token;
    }

    public function pullForUser(string $token, int $userId): ?array
    {
        return $this->cache()->lock("subject-import:result-lock:{$token}", 10)->block(3, function () use ($token, $userId) {
            $payload = $this->cache()->get($this->key($token));
            if (! is_array($payload) || $payload['user_id'] !== $userId) {
                return null;
            }
            $this->cache()->forget($this->key($token));

            return $payload['result'];
        });
    }
}
