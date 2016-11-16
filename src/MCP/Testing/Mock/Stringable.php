<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Testing\Mock;

/**
 * @codeCoverageIgnore
 */
class Stringable
{
    /**
     * @var mixed
     */
    protected $output;

    /**
     * @param mixed $output
     */
    public function __construct($output = '')
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->output;
    }
}
