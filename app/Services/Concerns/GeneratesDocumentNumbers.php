<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesDocumentNumbers
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function generateDocumentNumber(
        string $modelClass,
        string $numberColumn,
        int $userId,
        string $modulePrefixKey,
        string $modulePrefixDefault
    ): string {
        $year = now()->format('Y');
        $documentPrefix = $this->buildDocumentPrefix($userId, $modulePrefixKey, $modulePrefixDefault);
        $searchPattern = sprintf('%s-%s-%%', $documentPrefix, $year);

        $lastRecord = $modelClass::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where($numberColumn, 'like', $searchPattern)
            ->orderByDesc('id')
            ->first([$numberColumn]);

        $sequence = $lastRecord
            ? (int) Str::afterLast((string) $lastRecord->{$numberColumn}, '-') + 1
            : 1;

        return sprintf('%s-%s-%03d', $documentPrefix, $year, $sequence);
    }

    protected function buildDocumentPrefix(int $userId, string $modulePrefixKey, string $modulePrefixDefault): string
    {
        $companyPrefix = $this->resolveCompanyPrefix($userId);
        $modulePrefix = $this->sanitizePrefix(
            (string) setting_for_user($userId, $modulePrefixKey, $modulePrefixDefault),
            $modulePrefixDefault,
        );

        return sprintf('%s-%s', $companyPrefix, $modulePrefix);
    }

    protected function resolveCompanyPrefix(int $userId): string
    {
        $configured = $this->sanitizePrefix((string) setting_for_user($userId, 'company_prefix', ''), '');
        if ($configured !== '') {
            return $configured;
        }

        $businessName = (string) setting_for_user($userId, 'business_name', 'Invoice Pro');
        $derived = $this->deriveFromBusinessName($businessName);

        return $derived !== '' ? $derived : 'CO';
    }

    protected function deriveFromBusinessName(string $businessName): string
    {
        $words = preg_split('/[^A-Za-z0-9]+/', strtoupper(trim($businessName)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) >= 2) {
            return substr($words[0], 0, 1).substr($words[1], 0, 1);
        }

        if (count($words) === 1) {
            $prefix = substr($words[0], 0, 2);

            return strlen($prefix) === 1 ? $prefix.'X' : $prefix;
        }

        return '';
    }

    protected function sanitizePrefix(string $value, string $fallback): string
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($value)) ?? '');

        if ($normalized !== '') {
            return $normalized;
        }

        return strtoupper($fallback);
    }
}
