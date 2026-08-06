<?php

namespace Database\Factories\Support;

use RuntimeException;

class ScammerNamePool
{
    /** @var list<string>|null */
    private static ?array $names = null;

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        self::load();

        return self::$names;
    }

    private static function load(): void
    {
        if (self::$names !== null) {
            return;
        }

        $path = database_path('data/historical_scammers.csv');

        if (! is_readable($path)) {
            throw new RuntimeException("Historical scammers CSV not readable: {$path}");
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open historical scammers CSV: {$path}");
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new RuntimeException("Historical scammers CSV is empty: {$path}");
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0] ?? '') ?? '';

            $nameIndex = array_search('Nombre_Completo', $header, true);

            if ($nameIndex === false) {
                throw new RuntimeException('Historical scammers CSV must have a Nombre_Completo column');
            }

            $names = [];

            while (($row = fgetcsv($handle)) !== false) {
                $name = trim((string) ($row[$nameIndex] ?? ''));

                if ($name !== '') {
                    $names[] = $name;
                }
            }

            if ($names === []) {
                throw new RuntimeException("Historical scammers CSV has no usable rows: {$path}");
            }

            self::$names = $names;
        } finally {
            fclose($handle);
        }
    }
}
