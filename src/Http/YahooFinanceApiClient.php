<?php


namespace App\Http;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient implements YahooFinanceApiClientInterface
{
    private HttpClientInterface $httpClient;

    private const API_URL='https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v2/get-profile';
    private const X_RAPID_API_HOST = 'apidojo-yahoo-finance-v1.p.rapidapi.com';
    private string $rapidAPiKey;

    public function __construct(HttpClientInterface $httpClient, string $rapidAPiKey)
    {
        $this->httpClient = $httpClient;
        $this->rapidAPiKey = $rapidAPiKey;
    }

    public function fetchStockProfile(string $symbol, string $region): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => [
                'symbol' => $symbol,
                'region' => $region
            ],
            'headers' => [
                'x-rapidapi-key' => $this->rapidAPiKey,
                'x-rapidapi-host' => self::X_RAPID_API_HOST
            ]
        ]);

        if (200 !== $response->getStatusCode()) {
            // todo handle non 200 http status code
        }

        $stockProfile = json_decode($response->getContent())->price;

        $stockProfileArray = [
            'symbol' => $stockProfile->symbol,
            'shortName' => $stockProfile->shortName,
            'region' => $region,
            'exchangeName' => $stockProfile->exchangeName,
            'currency' => $stockProfile->currency,
            'price' => $stockProfile->regularMarketPrice->row,
            'priceChange' => $stockProfile->regularMarketPreviousClose->row,
            'previousClose' => $stockProfile->regularMarketPrice->row - $stockProfile->regularMarketPreviousClose->row
        ];

        return new JsonResponse($stockProfileArray, 200);
    }
}