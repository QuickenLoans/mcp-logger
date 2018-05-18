<?php
return [
    'id' => \QL\MCP\Common\GUID::createFromHex('9E43E37AD36C4F88B142A417670C93CF'),

    'created' => (new \QL\MCP\Common\Clock('2016-11-08 16:00:00', 'UTC'))->read(),

    'details' => 'beep boop',
    'context' => [
        'key' => 'value',
        'key2' => 'value2',
        'key value 3' => 'value3'
    ],

    'applicationID' => '10',
    'serverEnvironment' => 'test',

    'serverIP' => '127.0.0.1',
    'serverHostname' => 'Deathstar',

    'requestMethod' => 'DELETE',
    'requestURL' => 'http://localhost/spaaaaaace',

    'userAgent' => 'X-Wing',
    'userIP' => '192.168.0.100',
];
