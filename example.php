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
    } catch (InvalidArgumentException $e) {
        print("Invalid data - {$e->getMessage()}");
    } catch (Exception $e) {
        print("Did not get a valid response from Steam.");
    }
} else {
    // No openid parameters in request; show login form
    ?>

    <form action="https://steamcommunity.com/openid/login" method="post">
        <input type="hidden" name="openid.identity" value="http://specs.openid.net/auth/2.0/identifier_select">
        <input type="hidden" name="openid.claimed_id" value="http://specs.openid.net/auth/2.0/identifier_select">
        <input type="hidden" name="openid.ns" value="http://specs.openid.net/auth/2.0">
        <input type="hidden" name="openid.mode" value="checkid_setup">
        <input type="hidden" name="openid.realm" value="http://<?=$_SERVER['HTTP_HOST']?>">
        <input type="hidden" name="openid.return_to" value="<?=$client->getReturnUrl()?>">
        <input type="image" name="submit"
               src="https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png" border="0"
               alt="Submit">
    </form>
    <?php
}
