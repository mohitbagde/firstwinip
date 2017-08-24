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
    /** @var  string $endpoint */
    private $endpoint = 'http://www.example.com';
    /**
     * @var string $apiKey
     */
    private $apiKey = 'api-key';
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
        $summonerName = 'dingus';
        $actualJSONResponse = '{"data":"dunkey-beat-sky-in-smash"}';
        $this->handler->append(
            new Response(200, [], $actualJSONResponse)
        );
        $summonerData = $this->client->getSummonerData($summonerName);

        $this->assertSame((array) json_decode($actualJSONResponse), $summonerData);
    }

    public function providesMockInvalidSummonerData()
    {
        return [
            'not-found-data' => [
                new Response(404, [], false),
                'carlton-banks',
            ],
            'internal-server-error' => [
                new Response(500, [], false),
                'pizza-dog',
            ],
            'unprocessable-entity' => [
                new Response(422, [], false),
                'bubber-ducky',
            ],
        ];
    }

    /**
     * @dataProvider providesMockInvalidSummonerData
     * @param Response $response
     * @param string $summonerName
     *
     */
    public function testInvalidSummonerDataResponse($expectedResponse, $summonerName)
    {
        $this->handler->append($expectedResponse);
        $this->expectException(RiotException::class);

        $this->client->getSummonerData($summonerName);
    }
}
