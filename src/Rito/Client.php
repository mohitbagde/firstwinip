<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use src\Exception as RiotException;

class RiotAPI {

	/** @var  GuzzleHttp\Client $client */
	private $client;

	/** @var  \GuzzleHttp\HandlerStack $handler */
	private $handler;

	/** @var  string $endpoint */
	private $endpoint;

	/** @var string $apiKey */
	private $apiKey;

	/**
	 * RiotAPI constructor.
	 * @param string $endpoint
	 * @param HandlerStack|null $handler
	 * @param string $apiKey
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($endpoint, $apiKey, HandlerStack $handler = null) {
		$this->handler = $handler ?: HandlerStack::create();

		// Ensure endpoint exists
		if (!(empty($endpoint))
			|| (empty($apiKey))
		) {
			$this->endpoint = $endpoint;
			$this->apiKey = $apiKey;
		}
		else {
			throw new InvalidArgumentException('Error: Check OAuth params');
		}

		$this->client = new Client([
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
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * Get a summoner's details
	 * @param $summonerName
	 *
	 * @return array
	 */
	public function getSummonerData($summonerName) {
		// Set the path
		$path = new Uri(
			sprintf('/lol/summoner/v3/summoners/by-name/%s?api_key=',
				$summonerName, $this->apiKey)
		);

		// Build the request
		$request = new Request('GET', $path);

		/** @var ResponseInterface $response */
		return $this->executeRequest($request);

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
