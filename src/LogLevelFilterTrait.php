<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger;

/**
 * A trait to assist in the filtering of messages by log level. This trait errs on the side of caution and will
 * not filter messages when incorrectly configured.
 */
trait LogLevelFilterTrait
{
    private $levels = [
        LogLevelInterface::AUDIT => 5,
        LogLevelInterface::FATAL => 4,
        LogLevelInterface::ERROR => 3,
        LogLevelInterface::WARN => 2,
        LogLevelInterface::INFO => 1,
        LogLevelInterface::DEBUG => 0
    ];

    /**
     * The minimum logging level.
     *
     * This value defaults to LogLevelInterface::DEBUG, so no messages will be filtered.
     *
     * @var string
     */
    private $minimum = LogLevelInterface::DEBUG;

    /**
     * Set the minimum logging level.
     *
     * When passing this value, you must make sure that it is a valid level as defined by the LogLevelInterface. Any
     * other value will result in LogLevelInterface::DEBUG being set as the minimum value, and all messages will be
     * logged.
     *
     * @param string $minimum
     */
    private function setMinimumLevel($minimum)
    {
        $this->minimum = array_key_exists($minimum, $this->levels) ? $minimum : LogLevelInterface::DEBUG;
    }

    /**
     * Get the minimum logging level
     *
     * @return string
     */
    private function getMinimumLevel()
    {
        return $this->minimum;
    }

    /**
     * Return true if the provided log level meets or exceeds the minimum logging level.
     *
     * When passing this value, you must make sure that it is a valid level as defined by the LogLevelInterface. Any
     * other value will result in true being returned.
     *
     * @param string $level
     * @return bool
     */
    private function shouldLog($level)
    {
        if (array_key_exists($level, $this->levels) && $this->levels[$level] < $this->levels[$this->minimum]) {
            return false;
        }

        return true;
    }
}
