<?php

declare(strict_types=1);

namespace BVP\Tenjisagi;

use RuntimeException;

/**
 * @author shimomo
 */
final class JsonEncoder
{
    /**
     * @param array $payload
     * @return string
     * @throws \RuntimeException
     */
    public static function encode(array $payload): string
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'Failed to encode payload to JSON: ' . json_last_error_msg()
            );
        }

        return $json;
    }
}
