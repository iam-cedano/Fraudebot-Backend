<?php

namespace Database\Factories\Support;

use RuntimeException;

class ScamEnterprisePool
{
    /** @var list<string>|null */
    private static ?array $companyNames = null;

    /** @var list<string>|null */
    private static ?array $markets = null;

    /**
     * @return list<string>
     */
    public static function companyNames(): array
    {
        self::load();

        return self::$companyNames;
    }

    /**
     * @return list<string>
     */
    public static function markets(): array
    {
        self::load();

        return self::$markets;
    }

    private static function load(): void
    {
        if (self::$companyNames !== null) {
            return;
        }

        $path = database_path('data/scam_enterprises.csv');

        if (! is_readable($path)) {
            throw new RuntimeException("Scam enterprise CSV not readable: {$path}");
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open scam enterprise CSV: {$path}");
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new RuntimeException("Scam enterprise CSV is empty: {$path}");
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0] ?? '') ?? '';

            $companyIndex = array_search('Company_Name', $header, true);
            $marketIndex = array_search('Market', $header, true);

            if ($companyIndex === false || $marketIndex === false) {
                throw new RuntimeException('Scam enterprise CSV must have Company_Name and Market columns');
            }

            $companyNames = [];
            $markets = [];

            while (($row = fgetcsv($handle)) !== false) {
                $company = trim((string) ($row[$companyIndex] ?? ''));
                $market = trim((string) ($row[$marketIndex] ?? ''));

                if ($company !== '') {
                    $companyNames[] = $company;
                }

                if ($market !== '') {
                    $markets[$market] = $market;
                }
            }

            if ($companyNames === [] || $markets === []) {
                throw new RuntimeException("Scam enterprise CSV has no usable rows: {$path}");
            }

            self::$companyNames = $companyNames;
            self::$markets = array_values($markets);
        } finally {
            fclose($handle);
        }
    }
}
