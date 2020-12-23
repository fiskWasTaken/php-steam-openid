<?php
declare(strict_types=1);
require("SteamOpenID.php");

$client = new \SteamOpenID\SteamOpenID('https://localhost/login/example.php');

if (array_key_exists('openid_claimed_id', $_GET)) {
    try {
        $result = $client->validate();
        print("Signed in as {$result}!");
    } catch (InvalidArgumentException $e) {
        print("Invalid data - {$e->getMessage()}");
    } catch (Exception $e) {
        print("Did not get a valid response from Steam.");
    }
} else {
    // Show login form
    ?>
    <form action="https://steamcommunity.com/openid/login" method="post">
        <input type="hidden" name="openid.identity" value="http://specs.openid.net/auth/2.0/identifier_select">
        <input type="hidden" name="openid.claimed_id" value="http://specs.openid.net/auth/2.0/identifier_select">
        <input type="hidden" name="openid.ns" value="http://specs.openid.net/auth/2.0">
        <input type="hidden" name="openid.mode" value="checkid_setup">
        <input type="hidden" name="openid.realm" value="https://localhost/">
        <input type="hidden" name="openid.return_to" value="<?=$client->getReturnUrl()?>">
        <input type="image" name="submit"
               src="https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png" border="0"
               alt="Submit">
    </form>
    <?php
}
