<?php
namespace Playground\Rito;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use Playground\Exception as RiotException;
use InvalidArgumentException;

class Client
{

    const EXCEPTION_MESSAGE = 'Error: Endpoint and apiKey must be passed';

    /** @var  GuzzleClient $client */
    private $client;

    /** @var  \GuzzleHttp\HandlerStack $handler */
    private $handler;

    /** @var  string $endpoint */
    private $endpoint;

    /** @var string $apiKey */
    private $apiKey;

    /**
     * Client constructor.
     * @param string $endpoint
     * @param HandlerStack|null $handler
     * @param string $apiKey
     *
     * @throws InvalidArgumentException
     */
    public function __construct($endpoint, $apiKey, HandlerStack $handler = null)
    {
        $this->handler = $handler ?: HandlerStack::create();

        // Ensure endpoint exists
        if (!(empty($endpoint)
            || empty($apiKey))
        ) {
            $this->endpoint = $endpoint;
            $this->apiKey = $apiKey;
        } else {
            throw new InvalidArgumentException(self::EXCEPTION_MESSAGE);
        }

        $this->client = new GuzzleClient([
            'base_uri' => $endpoint,
            'handler' => $this->handler,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Return endpoint.
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Return the Riot API key.
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Get a summoner's details
     * @param $summonerName
     *
     * @return array
     */
    public function getSummonerData($summonerName)
    {
        // Set the path
        $path = new Uri(
            sprintf(
                '/lol/summoner/v3/summoners/by-name/%s?api_key=%s',
                $summonerName,
                $this->apiKey
            )
        );

        // Build the request
        $request = new Request('GET', $path);

        /** @var array $response */
        return $this->executeRequest($request);
    }

    /**
     * Get a summoner's account Id from their data
     * @param $summonerName
     * @return bool|int
     */
    public function getAccountId($summonerName)
    {
        $summonerData = $this->getSummonerData($summonerName);
        return isset($summonerData['accountId']) ? (int) $summonerData['accountId'] : false;
    }

    /**
     * Get a summoner's match data
     * @param $accountId
     *
     * @return array
     */
    public function getMatchHistory($accountId)
    {
        $path = new Uri(
            sprintf(
                '/lol/match/v3/matchlists/by-account/%s/recent?api_key=%s',
                $accountId,
                $this->apiKey
            )
        );

        // Build the request
        $request = new Request('GET', $path);

        /** @var array $response */
        return $this->executeRequest($request);
    }

    /**
     * Get a summoner's gameId from their recent matchdata
     * @param $accountId
     *
     * @return bool|string
     */
    public function getGameId($accountId)
    {
        return $this->getMatchHistory($accountId)['matches'][0]['gameId'] ?: false;
    }
    /**
     * Get the most recent victory
     * @param $gameId
     * @return array
     */
    public function getMostRecentWinInfo($gameId)
    {
        $path = new Uri(
            sprintf(
                '/lol/match/v3/matches/%s?api_key=%s',
                $gameId,
                $this->apiKey
            )
        );

        // Build the request
        $request = new Request('GET', $path);

        /** @var array $response */
        $matchInfo = $this->executeRequest($request);
        $data = [
            'time' => $matchInfo['gameCreation'] + $matchInfo['gameDuration']
        ];

        $participantId = false;
        $allSummonerData = $matchInfo['participantIdentities'];
        array_filter($allSummonerData, function ($datum) use (&$summonerName, &$participantId) {
            if (strcasecmp($datum->player->summonerName, $summonerName) == 0) {
                $participantId = $datum->participantId;
                return true;
            }
            return false;
        });

        if ($participantId) {
            array_filter($matchInfo['participants'], function ($datum) use (&$status, &$participantId) {
                if (strcasecmp($datum->stats->participantId, $participantId) == 0) {
                    $data['status'] = $datum->stats->win;
                    return true;
                }
                return false;
            });
        }

        return $data;
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return array
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws RiotException
     */
    private function executeRequest(RequestInterface $request, array $options = [])
    {
        try {
            $response = $this->client->send($request, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw RiotException::fromRequestException($e, 'API error: ');
        }
    }
}
