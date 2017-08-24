<?php

namespace Unit\Rito;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Playground\Rito\Client as RiotClient;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    /** @var  string $endpoint */
    private $endpoint;
    /**
     * @var string $apiKey
     */
    private $apiKey;
    /**
     * @var MockHandler $handler
     */
    private $handler;
    /**
     * @var RiotClient $client
     */
    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->handler = new MockHandler();
        $this->apiKey = 'api-key';
        $this->endpoint = 'http://www.paparito.com';
        $this->client = new RiotClient(
            $this->endpoint,
            $this->apiKey,
            HandlerStack::create($this->handler)
        );
    }

    public function testConstructor()
    {
        $riotClient = new RiotClient(
            $this->endpoint,
            $this->apiKey,
            HandlerStack::create($this->handler)
        );

        $this->assertInstanceOf(RiotClient::class, $riotClient);
        $this->assertEquals($this->endpoint, $riotClient->getEndpoint());
        $this->assertEquals($this->apiKey, $riotClient->getApiKey());
    }

    public function testRitoClient()
    {
        $summonerName = 'dunkey';
        $this->handler->append(
            new Response(200, [], '{"data":"summoner-info"}')
        );
        $summonerData = $this->client->getSummonerData($summonerName);

        $this->assertSame((array)json_decode('{"data":"summoner-info"}'), $summonerData);
    }
}
