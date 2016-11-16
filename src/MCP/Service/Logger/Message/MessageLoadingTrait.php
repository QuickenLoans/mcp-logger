<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Service\Logger\Message;

use BadFunctionCallException;
use InvalidArgumentException;

/**
 * @internal
 */
trait MessageLoadingTrait
{
    /**
     * @param string $name
     * @param array $inputData
     * @param boolean $isRequired
     * @param mixed $default
     * @throws InvalidArgumentException
     * @return mixed
     */
    private function parseLevel($name, array $inputData, $isRequired = false, $default = null)
    {
        if (isset($inputData[$name])) {
            $level = ucfirst(strtolower($inputData[$name]));
            if ($this->isValidLevel($level)) {
                return $level;
            }

            throw new InvalidArgumentException(sprintf("'%s' is not a valid log level.", $inputData[$name]));
        }

        return $this->parseValue($name, $inputData, $isRequired, $default);
    }

    /**
     * @param string $level
     * @return boolean
     */
    private function isValidLevel($level)
    {
        return in_array(
            $level,
            array(
                static::DEBUG,
                static::INFO,
                static::WARN,
                static::ERROR,
                static::FATAL,
                static::AUDIT
            ),
            true
        );
    }

    /**
     * @param string $name
     * @param array $inputData
     * @param boolean $isRequired
     * @param mixed $default
     * @throws BadFunctionCallException
     * @return mixed
     */
    private function parseValue($name, array $inputData, $isRequired = false, $default = null)
    {
        if (isset($inputData[$name])) {
            return $inputData[$name];

        } elseif ($isRequired) {
            throw new BadFunctionCallException(sprintf("'%s' is required.", $name));
        }

        return $default;
    }

    /**
     * @param string $name
     * @param array $inputData
     * @param boolean $isRequired
     * @param boolean $default
     * @return boolean
     */
    private function parseBoolean($name, array $inputData, $isRequired = false, $default = false)
    {
        if (isset($inputData[$name])) {
            return (bool) $inputData[$name];
        }

        return $this->parseValue($name, $inputData, $isRequired, (bool) $default);
    }

    /**
     * @param string $name
     * @param array $inputData
     * @param string $type
     * @param boolean $isRequired
     * @param IPv4Address|null $default
     * @throws InvalidArgumentException
     * @return mixed
     */
    private function parseClassType($name, array $inputData, $type, $isRequired = false, $default = null)
    {
        if (isset($inputData[$name]) && !$inputData[$name] instanceof $type) {
            throw new InvalidArgumentException(sprintf("'%s' must be an instance of '%s'.", $name, $type));
        }

        return $this->parseValue($name, $inputData, $isRequired, $default);
    }

    /**
     * @param string $name
     * @param array $inputData
     * @param boolean $isRequired
     * @param array $default
     * @throws InvalidArgumentException
     * @return mixed
     */
    private function parseProperties($name, array $inputData, $isRequired = false, $default = null)
    {
        if (isset($inputData[$name])) {
            if (!is_array($inputData[$name])) {
                throw new InvalidArgumentException(sprintf("'%s' must be an instance of '%s'.", $name, 'array'));
            }

            foreach ($inputData[$name] as $key => $value) {
                if (is_int($key)) {
                    throw new InvalidArgumentException('Extended Properties must use named keys.');
                }

                if (is_array($value)) {
                    throw new InvalidArgumentException('Extended Properties must not nest arrays.');
                }

                if (!is_scalar($value) &&
                    !(is_object($value) && is_callable(array($value, '__toString')))
                ) {
                    throw new InvalidArgumentException(
                        'Extended Properties must be scalars or objects that implement __toString.'
                    );
                }
            }
        }

        return $this->parseValue($name, $inputData, $isRequired, $default);
    }
}
