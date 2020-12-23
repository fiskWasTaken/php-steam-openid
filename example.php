<?php
declare(strict_types=1);
require("vendor/autoload.php");

// after signing in, the gateway will return to this example page
$client = new \SteamOpenID\SteamOpenID("http://" . $_SERVER['HTTP_HOST']);

if ($client->hasResponse()) {
    // A response has been returned, try to process it.
    try {
        $result = $client->validate();
        print("Signed in as {$result}!");
    } catch (Exception $e) {
        print($e->getMessage());
    }
} else {
    // redirect to steam
    header('Location: '.$client->getAuthUrl());
}
