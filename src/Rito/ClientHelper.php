<?php
/**
 * Created by PhpStorm.
 * User: mohit_bagde
 * Date: 8/24/17
 * Time: 6:20 PM
 */

namespace Playground\Rito;

class ClientHelper
{
    public static function getRiotRegions()
    {
        return [
            'BR' => 'https://br1.api.riotgames.com',
            'EUNE' => 'https://eun1.api.riotgames.com',
            'EUW' => 'https://euw1.api.riotgames.com',
            'JP' => 'https://jp1.api.riotgames.com',
            'KR' => 'https://kr.api.riotgames.com',
            'LAN' => 'https://la1.api.riotgames.com',
            'LAS' => 'https://la2.api.riotgames.com',
            'NA' => 'https://na1.api.riotgames.com',
            'OCE' => 'https://oc1.api.riotgames.com',
            'TR' => 'https://tr1.api.riotgames.com',
            'RU' => 'https://ru.api.riotgames.com',
            'PBE' => 'https://pbe1.api.riotgames.com',
        ];
    }
}
