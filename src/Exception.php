<?php

namespace src;

use GuzzleHttp\Exception\RequestException;

/**
 * Generic Client exceptions
 */
class Exception extends \RuntimeException
{
    /**
     * @param RequestException $e
     * @param string $prefix
     * @return RequestException|Exception
     */
    public static function fromRequestException(RequestException $e, $prefix)
    {
        $response = $e->getResponse();

        if ($response === null) {
            return $e;
        }
        //Return the raw error in JSON format
        $errorMessage = $response->getBody()->getContents();
        return new static($prefix . $errorMessage, 0, $e);
    }
}
