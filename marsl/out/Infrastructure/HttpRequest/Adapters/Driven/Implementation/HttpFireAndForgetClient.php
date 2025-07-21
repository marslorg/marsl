<?php

namespace marsl\Infrastructure\HttpRequest\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use Exception;
use GuzzleHttp\Client;
use marsl\Infrastructure\HttpRequest\Ports\Driven\IHttpFireAndForgetClient;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

class HttpFireAndForgetClient implements IHttpFireAndForgetClient
{
    /**
     * Sends a POST request to the specified URL with the given parameters and data.
     *
     * @param Url $url The URL to which the request is sent.
     * @param array<string, ParamValue> $params The parameters to include in the request.
     * @param array<string, DataValue> $data The data to include in the request body.
     * @return bool Returns true if the request was sent successfully, false otherwise.
     */
    public function postRequest(Url $url, array $params, array $data): bool
    {
        $client = new Client([
            'read_timeout'  => 0.1,
        ]);

        $urlString = $url->getValue();
        $query = http_build_query($this->prepareParamArray($params));
        if (!empty($query)) {
            $urlString .= '?' . $query;
        }

        try {
            $client->request('POST', $urlString, [
                'form_params' => $this->prepareDataArray($data)
            ]);
        } catch (Exception $e) {
            // Log the exception or handle it as needed
            // For now, we just return false to indicate failure
            return false;
        }

        return true;
    }

    /**
     * Prepares the data array for the HTTP request.
     *
     * @param array<string, DataValue> $data
     * @return array<string, string>
     */
    private function prepareDataArray(array $data): array
    {
        $preparedData = [];
        foreach ($data as $key => $value) {
            $preparedData[$key] = $value->getValue();
        }
        return $preparedData;
    }

    /**
     * Prepares the parameters array for the HTTP request.
     *
     * @param array<string, ParamValue> $data
     * @return array<string, string>
     */
    private function prepareParamArray(array $data): array
    {
        $preparedData = [];
        foreach ($data as $key => $value) {
            $preparedData[$key] = $value->getValue();
        }
        return $preparedData;
    }
}
