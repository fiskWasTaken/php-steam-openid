<?php

namespace SteamOpenID;

use Exception;
use InvalidArgumentException;

/**
 * SteamOpenID client class.
 *
 * Original implementation by xPaw. This has been modified to suit PSR-7 applications, etc that might need to override
 * input parmeters
 *
 * @author xPaw, fisk
 * @license MIT
 */
class SteamOpenID
{
    private $returnTo;
    private $params;

    /**
     * SteamOpenID constructor.
     *
     * @param string $returnTo callback URL when returning from OpenID gateway
     * @param ?array $params request parameters provided in the returned query string. Only required if the application serves requests without setting $_GET
     */
    public function __construct(string $returnTo, ?array $params = null)
    {
        $this->returnTo = $returnTo;
        $this->params = $params ?? $_GET;
    }

    /**
     * Returns true if the endpoint has received a positive assertion from the gateway.
     * If false, the client should redirect the user to the Steam OpenID gateway.
     *
     * @return bool
     */
    public function hasResponse(): bool
    {
        return ($this->params['openid_mode'] ?? '') === 'id_res';
    }

    /**
     * Return URL; the gateway will return information to this endpoint.
     *
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnTo;
    }

    /**
     * The first part of the OpenID auth process is redirecting the user to the login gateway.
     *
     * @see http://openid.net/specs/openid-authentication-2_0.html#positive_assertions
     * @return string
     */
    public function getAuthUrl(): string
    {
        return "https://steamcommunity.com/openid/login?" . http_build_query([
                'openid.ns' => 'http://specs.openid.net/auth/2.0',
                'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
                'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
                'openid.mode' => 'checkid_setup',
                'openid.return_to' => $this->returnTo
            ]);
    }

    /**
     * Validates OpenID data, and verifies with Steam.
     *
     * @return string 64-bit Steam Community ID
     * @throws InvalidArgumentException if request parameters appear to be tampered with.
     * @throws Exception the nonce was already used, or there is a problem with the Steam gateway.
     */
    public function validate(): string
    {
        // check constraints that we always expect to be constant.
        $constraints = [
            'openid_ns' => 'http://specs.openid.net/auth/2.0',
            'openid_op_endpoint' => 'https://steamcommunity.com/openid/login',
            'openid_mode' => 'id_res',
        ];

        foreach ($constraints as $key => $expected) {
            $actual = $this->params[$key] ?? null;

            if ($actual !== $expected) {
                throw new InvalidArgumentException("expected '{$key}' to be '{$expected}', was '{$actual}'");
            }
        }

        $arguments = $this->getArguments();

        foreach ($arguments as $key => $value) {
            // An array value will be FALSE if the filter fails, or NULL if the variable is not set.
            // In our case we want everything to be a string.
            if (!is_string($value)) {
                $actual = ($this->params[$key] ?? 'null');
                throw new InvalidArgumentException("'{$key}' failed to meet filter criteria (value was {$actual})");
            }
        }

        if (strpos($arguments['openid_return_to'], $this->returnTo) !== 0) {
            throw new InvalidArgumentException("expected {$this->returnTo}, actual {$arguments['return_to']}");
        }

        if ($arguments['openid_claimed_id'] !== $arguments['openid_identity']) {
            throw new InvalidArgumentException("claimed_id and identity should match ({$arguments['openid_claimed_id']}, {$arguments['openid_identity']}");
        }

        if (preg_match('/^https?:\/\/steamcommunity.com\/openid\/id\/(7656119[0-9]{10})\/?$/',
                $arguments['openid_identity'], $communityId) !== 1) {
            throw new InvalidArgumentException("openid_identity does not appear to contain a valid Steam Community ID ({$arguments['openid_identity']})");
        }

        $arguments['openid_mode'] = 'check_authentication';

        $c = curl_init();

        curl_setopt_array($c, [
            CURLOPT_USERAGENT => 'OpenID Verification (+https://github.com/fisuku/php-steam-openid)',
            CURLOPT_URL => 'https://steamcommunity.com/openid/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $arguments,
        ]);

        $response = curl_exec($c);

        curl_close($c);

        if ($response !== false && strrpos($response, 'is_valid:true') !== false) {
            return $communityId[1];
        }

        throw new Exception("did not receive a valid response from check_authentication call");
    }

    /**
     * @see http://openid.net/specs/openid-authentication-2_0.html#positive_assertions
     * @return array
     */
    protected function getArguments(): array
    {
        return filter_var_array($this->params, [
            'openid_ns' => FILTER_SANITIZE_URL,
            'openid_op_endpoint' => FILTER_SANITIZE_URL,
            'openid_claimed_id' => FILTER_SANITIZE_URL,
            'openid_identity' => FILTER_SANITIZE_URL,
            'openid_return_to' => FILTER_SANITIZE_URL, // Should equal to url we sent
            'openid_response_nonce' => FILTER_SANITIZE_STRING,
            'openid_assoc_handle' => FILTER_SANITIZE_SPECIAL_CHARS, // Steam just sends 1234567890
            'openid_signed' => FILTER_SANITIZE_SPECIAL_CHARS,
            'openid_sig' => FILTER_SANITIZE_SPECIAL_CHARS
        ], true);
    }
}
