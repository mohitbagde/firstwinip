<?php

namespace Unit\Rito;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Playground\Rito\Client;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Response;
use Playground\Rito\Client as RiotClient;
use Playground\Exception as RiotException;
use InvalidArgumentException;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    /** @var  string $accountId */
    private $accountId;

    /** @var string $apiKey */
    private $apiKey;

    /** @var string $endpoint */
    private $endpoint;

    /** @var MockHandler $handler */
    private $handler;

    /** @var string $summonerName */
    private $summonerName;

    /** @var RiotClient $client */
    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->accountId = 123;
        $this->apiKey = 'api-key';
        $this->endpoint = 'http://www.paparito.com';
        $this->handler = new MockHandler();
        $this->summonerName = 'dingus';

        $this->client = new RiotClient($this->endpoint, $this->apiKey, HandlerStack::create($this->handler));
    }

    public function providesMockConstructorData()
    {
        return [
            'valid-response' => [
                'based-rito.com',
                'papa-bless',
                false,
            ],
            'empty-endpoint' => [
                '',
                'papa-bless',
                true,
            ],
            'empty-apikey' => [
                'based-rito.com',
                '',
                true,
            ],
        ];
    }

    /**
     * @dataProvider providesMockConstructorData
     * @param string $endpoint
     * @param
     */
    public function testConstructor($endpoint, $apiKey, $throwsException)
    {
        if ($throwsException) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage(Client::EXCEPTION_MESSAGE);
        }

        $riotClient = new RiotClient($endpoint, $apiKey, HandlerStack::create($this->handler));

        $this->assertInstanceOf(RiotClient::class, $riotClient);
        $this->assertEquals($endpoint, $riotClient->getEndpoint());
        $this->assertEquals($apiKey, $riotClient->getApiKey());
    }

    public function testValidSummonerDataResponse()
    {
        $actualJSONResponse = '{"data":"dunkey-beat-sky-in-smash"}';
        $this->handler->append(new Response(200, [], $actualJSONResponse));
        $summonerData = $this->client->getSummonerData($this->summonerName);

        $this->assertSame((array)json_decode($actualJSONResponse), $summonerData);
    }

    public function providesMockInvalidSummonerData()
    {
        return [
            'not-found-data' => [
                new Response(404, [], false),
            ],
            'internal-server-error' => [
                new Response(500, [], false),
            ],
            'unprocessable-entity' => [
                new Response(422, [], false),
            ],
        ];
    }

    /**
     * @dataProvider providesMockInvalidSummonerData
     * @param Response $expectedResponse
     */
    public function testInvalidSummonerDataResponse($expectedResponse)
    {
        $this->handler->append($expectedResponse);
        $this->expectException(RiotException::class);

        $this->client->getSummonerData($this->summonerName);
    }

    public function providesMockSummonerData()
    {
        return [
            'valid-response' => [
                456, new Response(200, [], '{"accountId":"456"}')
            ],
        ];
    }

    /**
     * @dataProvider providesMockSummonerData
     * @param $mockSummonerDataResponse
     */
    public function testValidAccountId($expectedId, $mockSummonerDataResponse)
    {
        $this->handler->append($mockSummonerDataResponse);
        $this->assertSame($expectedId, $this->client->getAccountId($this->summonerName));
    }
}
