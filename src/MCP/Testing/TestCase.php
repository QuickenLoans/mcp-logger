<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Testing;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * Shamelessly stolen from ql/qa-toolchain
 *
 * @codeCoverageIgnore
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    const FIXTURES_DIR = FIXTURES_DIR;

    /**
     * @param string $relativePath
     * @return string
     */
    protected function loadRawFixture($relativePath)
    {
        $filePath = $this->getPath($relativePath);
        return file_get_contents($filePath);
    }

    /**
     * @param string $relativePath
     * @return string
     */
    protected function loadPhpFixture($relativePath)
    {
        $file = $this->loadRawFixture($relativePath);

        $code = 'return ' . $file . ';';
        return eval($code);
    }

    /**
     * @param string|null $basePath
     * @return string
     */
    protected function getFixturePath($basePath = null)
    {
        if ($basePath === null) {
            $basePath = static::FIXTURES_DIR;
        }

        $calledTest = explode('\\', get_called_class());
        $testName = end($calledTest);
        $class = substr($testName, 0, -4);
        array_splice($calledTest, -1, 1, $class);

        $fixturePath = array(
            rtrim($basePath, '/'),
        );

        $fixturePath = array_merge($fixturePath, $calledTest);
        return implode('/', $fixturePath);
    }

    /**
     * @param string $providedPath
     * @throws InvalidArgumentException
     * @return string
     */
    private function getPath($providedPath)
    {
        if ($providedPath[0] == '/') {
            $path = $providedPath;
        } else {
            $path = sprintf('%s/%s', $this->getFixturePath(), $providedPath);
        }

        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('No fixture found at %s', $path));
        }

        return $path;
    }
}
