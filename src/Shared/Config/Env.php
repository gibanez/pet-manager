<?php

declare(strict_types=1);

namespace Gaby\Shared\Config;

final class Env
{
    /** @var array<string, string>|null */
    private static $values = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        $values = self::load();

        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    public static function rootPath(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * @return array<string, string>
     */
    private static function load(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        $values = [];
        $file = self::rootPath() . DIRECTORY_SEPARATOR . '.env';

        if (is_file($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines === false ? [] : $lines as $line) {
                $line = trim($line);

                if ($line === '' || strpos($line, '#') === 0) {
                    continue;
                }

                $separator = strpos($line, '=');

                if ($separator === false) {
                    continue;
                }

                $key = trim(substr($line, 0, $separator));
                $value = trim(substr($line, $separator + 1));
                $first = $value === '' ? '' : $value[0];

                if (($first === '"' || $first === "'") && substr($value, -1) === $first) {
                    $value = substr($value, 1, -1);
                }

                if ($key !== '') {
                    $values[$key] = $value;
                }
            }
        }

        self::$values = $values;

        return $values;
    }
}
