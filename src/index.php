<?php

use Playground\Rito\Client;
use Playground\Rito\ClientHelper;

require_once __DIR__ . '../../conf.php';

$data = false;

if ($_POST) {
    // Sanitize the params
    $summonerName = preg_filter('/^[0-9\p{L} _\.]+$/', '', $_POST['summonerName']);
    $endpoint = filter_var($_POST['endpoint'], FILTER_SANITIZE_URL);

    try {
        // Get the API key from the env vars
        $riotClient = new Client($endpoint, $_POST['apiKey']);
        $data = $riotClient->getAccountId($summonerName);
    } catch (\Exception $e) {
        $data = sprintf('Riot API Error: %s', $e->getMessage());
    }
}
?>
<html>
<head>
    <title>PHP Playground 1.0.0</title>
</head>
<body>
<form method="POST" action="index.php">
    <h3>API Tester</h3><br>
    Endpoint:
    <select title="endpointSelector" name="endpoint">
        <option value=""><?="Select Region"?></option>
        <?php
        foreach (ClientHelper::getRiotRegions() as $regionCode => $regionDomain) { ?>
            <option value="<?=$regionDomain?>">
                <?=$regionCode?>
            </option>
        <?php } ?>
    </select>
    Summoner Name: <input type="text" title="SummonerName" name="summonerName">
    API Key: <input type="text" title="apiKey" name="apiKey">
    <input type="submit" name="submit" value="Submit"><br>
    <h3>API Response </h3>
    <?= $data ? json_encode($data, JSON_PRETTY_PRINT) : '' ?>
</form>
</body>
</html>
