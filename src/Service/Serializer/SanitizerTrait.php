<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Service\Serializer;

use QL\MCP\Common\GUID;
use QL\MCP\Common\IPv4Address;
use QL\MCP\Common\Time\TimePoint;

trait SanitizerTrait
{
    /**
     * @param bool $value
     *
     * @return bool
     */
    protected function sanitizeBoolean($value)
    {
        return (bool) $value;
    }

    /**
     * @param int|string $value
     *
     * @return string
     */
    protected function sanitizeInteger($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @param GUID $value
     *
     * @return string
     */
    protected function sanitizeGUID(GUID $value)
    {
        return $value->format(GUID::HYPENATED);
    }

    /**
     * @param IPv4Address|null $value
     *
     * @return string|null
     */
    protected function sanitizeIP($value)
    {
        if ($value instanceof IPv4Address) {
            return $value->asString();
        }

        return null;
    }

    /**
     * @param int|string $value
     *
     * @return string
     */
    protected function sanitizeString($value)
    {
        return filter_var((string) $value, FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_HIGH);
    }

    /**
     * @param TimePoint|null $value
     *
     * @return string|null
     */
    protected function sanitizeTime($value)
    {
        if ($value instanceof TimePoint) {
            return $value->format('Y-m-d\TH:i:s\.u\Z', 'UTC');
        }

        return null;
    }
}
