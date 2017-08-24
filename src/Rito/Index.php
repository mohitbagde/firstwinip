<?php

$form = [
    'summname' => [
        'strip' => '/^[0-9\p{L} _\.]+$/'
    ],
];
$endpoint = 'https://na1.api.riotgames.com';
$apiKey = 'RGAPI-64a9744e-91d2-4a32-b457-f926894be8c2';
$summonerName = '';
$riotClient = new Playground\Rito\Client($endpoint, $apiKey);

$data = $riotClient->getSummonerData($summonerName);

echo $data;
