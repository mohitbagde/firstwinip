<?php

namespace tests\Unit\Rito;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use src\Rito\Client as RiotClient;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    private $endpoint = 'http://example.com/orgservice';
    /**
     * @var string
     */
    private $apikey = 'api-key';
    /**
     * @var MockHandler
     */
    private $handler;
    /**
     * @var RiotClient
     */
    private $client;

    public function setUp()
    {
        parent::setUp();

        $this->handler = new MockHandler();
        $this->client = new RiotClient(
            $this->endpoint,
            $this->apikey,
            HandlerStack::create($this->handler)
        );
    }
}
