<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger\Transformer;

use Psr\Log\LogLevel;
use QL\MCP\Logger\MessageInterface;
use QL\MCP\Logger\Message\Message;
use QL\MCP\Logger\TransformerInterface;

class QLLogSeverityTransformer implements TransformerInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return MessageInterface
     */
    public function __invoke(MessageInterface $message): MessageInterface
    {
        $data = $message->all();

        $newLevel = $this->convertLogLevelFromPSRToQL($message->severity());

        return new Message($newLevel, $message->message(), $data);
    }

    /**
     * Translate a PRS-3 log level to QL log level
     *
     * Not used:
     *     - 'audit'
     *
     * @param string $severity
     *
     * @return string
     */
    private function convertLogLevelFromPSRToQL($severity)
    {
        // Equal mappings
        if ($severity === LogLevel::DEBUG) {
            return 'debug';

        } elseif ($severity === LogLevel::INFO) {
            return 'info';

        } elseif ($severity === LogLevel::WARNING) {
            return 'warn';

        } elseif ($severity === LogLevel::ERROR) {
            return 'error';
        }

        // Duplicate mappings
        if ($severity === LogLevel::NOTICE) {
            return 'info';

        } elseif ($severity === LogLevel::CRITICAL) {
            return 'fatal';

        } elseif ($severity === LogLevel::ALERT) {
            return 'fatal';

        } elseif ($severity === LogLevel::EMERGENCY) {
            return 'fatal';
        }

        // Default to error
        return 'error';
    }
}
