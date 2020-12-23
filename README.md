# Steam OpenID Authentication for PHP [![Build Status](https://travis-ci.org/fisuku/php-steam-openid.svg?branch=master)](https://travis-ci.org/fisuku/php-steam-openid)

```
composer require fisk/steam-openid
```

A simple, secure library for Steam OpenID clients. 

```php
$client = new \SteamOpenID\SteamOpenID("http://example.com");

if ($client->hasResponse()) {
    try {
        $result = $client->validate();
        print("signed in as {$result}");
    } catch (Exception $e) {
        print("error - {$e->getMessage()}");
    }
} else {
    // redirect the user to Steam, however this is done in your app
    redirect($client->getAuthUrl());
}
```

This library does not aim to be a completely universal OpenID client, as we just want it to meet our needs for the 
Steam OpenID gateway.

This is a fork of a library originally written by xPaw. The original project readme follows:

____

A very minimalistic OpenID implementation that hardcodes it for Steam only,
as using a generic OpenID library may do unnecessary steps of “discovering”
OpenID servers, which will end up leaking your origin server address, and worse,
leave your website open to vulnerabilities of claiming wrong Steam profiles if the implementation is bugged.

The described problems are not theoretical, LightOpenID
[has been proven](https://twitter.com/thexpaw/status/1088207320977412097)
to have implementation problems, and even if you use `validate` and use regex on the final
`identity` it can be spoofed and a third-party server can be used to pass the validation.

This library aims to avoid these problems by implementing the bare minimum functionality required
for validating Steam OpenID requests against the hardcoded Steam server. This library only implements
validation, you will need to implement Steam WebAPI calls yourself to fetch user profile information.

Before using this library, [please read Valve's terms here](https://steamcommunity.com/dev).
