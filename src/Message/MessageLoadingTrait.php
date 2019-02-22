<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Message;

use JsonSerializable;
use QL\MCP\Common\Clock;
use QL\MCP\Common\Time\TimePoint;
use QL\MCP\Logger\Exception;

trait MessageLoadingTrait
{
    /**
     * @var Clock
     */
    private static $createdTimeGenerator;

    /**
     * @return TimePoint
     */
    private function generateCreatedTime()
    {
        if (!self::$createdTimeGenerator) {
            self::$createdTimeGenerator = new Clock;
        }
        return self::$createdTimeGenerator->read();
    }

    /**
     * @param string $name
     * @param array $input
     * @param mixed $default
     *
     * @return mixed
     */
    private function parseValue($name, array $input, $default = null)
    {
        if (isset($input[$name])) {
            return $input[$name];
        }

        return $default;
    }

    /**
     * @param string $name
     * @param array $input
     *
     * @throws Exception
     *
     * @return mixed
     */
    private function parseRequiredValue($name, array $input)
    {
        if (isset($input[$name])) {
            return $input[$name];
        }

        throw new Exception(sprintf("'%s' is required.", $name));
    }

    /**
     * @param string $name
     * @param array $input
     * @param string $type
     * @param callable|mixed|null $default
     *
     * @throws Exception
     *
     * @return mixed
     */
    private function parseClass($name, array $input, $type, $default = null)
    {
        if (isset($input[$name])) {
            if (!$input[$name] instanceof $type) {
                throw new Exception(sprintf("'%s' must be an instance of '%s'.", $name, $type));
            }

            return $input[$name];
        }

        return is_callable($default) ? $default() : $default;
    }

    /**
     * @param string $name
     * @param array $input
     * @param string $type
     *
     * @throws Exception
     *
     * @return mixed
     */
    private function parseRequiredClass($name, array $input, $type)
    {
        if (isset($input[$name]) && $input[$name] instanceof $type) {
            return $input[$name];
        }

        throw new Exception(sprintf("'%s' must be an instance of '%s'.", $name, $type));
    }

    /**
     * @param string $name
     * @param array $input
     * @param array $default
     *
     * @throws Exception
     *
     * @return mixed
     */
    private function parseContext($name, array $input, $default = [])
    {
        if (isset($input[$name])) {
            if (!is_array($input[$name])) {
                throw new Exception(sprintf("'%s' must be an instance of '%s'.", $name, 'array'));
            }

            foreach ($input[$name] as $key => &$value) {
                if (is_int($key)) {
                    // Remove the data if it has no property name.
                    // If you try to pass a non-associative array as context, this will wipe that data.
                    unset($input[$name][$key]);
                    continue;
                }

                if (null === $value || is_bool($value)) {
                    $value = var_export($value, true);
                }

                // jsonify arrays and serializable classes
                if (is_array($value) || $value instanceof JsonSerializable) {
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                }

                // stringify scalars and stringable classes
                if (is_scalar($value) || (is_object($value) && is_callable([$value, '__toString']))) {
                    $value = (string) $value;

                } elseif (is_object($value)) {
                    $value = sprintf('[object] %s', get_class($value));
                }

                if (is_resource($value)) {
                    $value = '[resource]';
                }
            }

            return $input[$name];
        }

        return $default;
    }
}
