<?php

namespace SteamOpenID;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

/**
 * SteamOpenID client class.
 *
 * Original implementation by xPaw. This has been modified to suit PSR-7 applications, etc that might need to override
 * input parmeters
 *
 * @author xPaw, fisk
 * @license MIT
 */
class SteamOpenIDTest extends TestCase
{
    public function testInvalidOpenIDNamespaceException() {
        $client = new SteamOpenID("http://example.com", [
            'openid_ns' => 'http://bla.net'
        ]);

        $this->expectExceptionMessage("expected 'openid_ns' to be 'http://specs.openid.net/auth/2.0', was 'http://bla.net'");
        $client->validate();
    }

    public function testMalformedOpEndpointException() {
        $client = new SteamOpenID("http://example.com", [
            'openid_ns' => 'http://specs.openid.net/auth/2.0',
            'openid_op_endpoint' => 'https://illegal.com'
        ]);

        $this->expectExceptionMessage("expected 'openid_op_endpoint' to be 'https://steamcommunity.com/openid/login', was 'https://illegal.com'");
        $client->validate();
    }
}
