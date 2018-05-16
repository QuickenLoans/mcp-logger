<?php
return [
    'id' => \QL\MCP\Common\GUID::createFromHex('9E43E37AD36C4F88B142A417670C93CF'),
    'severity' => 'info',

    'message' => 'hello',
    'created' => new \QL\MCP\Common\Time\TimePoint(1977, 05, 25, 3, 0, 0, 'UTC'),

    'context' => [
        'key' => 'value',
        'key2' => 'value2'
    ],
    'details' => 'beep boop',

    'applicationID' => '10',
    'serverEnvironment' => 'test',

    'serverIP' => '127.0.0.1',
    'serverHostname' => 'Deathstar',

    'requestMethod' => 'DELETE',
    'requestURL' => 'http://localhost/spaaaaaace',

    'userAgent' => 'X-Wing',
    'userIP' => '192.168.0.100'
];
