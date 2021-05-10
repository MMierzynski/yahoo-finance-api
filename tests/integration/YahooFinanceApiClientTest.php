<?php


namespace App\Tests\Integration;


use App\Http\YahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;

class YahooFinanceApiClientTest extends DatabaseDependantTestCase
{

    /**
     * @group integration
     */
    public function testYahooApiClientReturnsTheCorrectData()
    {
        /**
        /**
         * @var YahooFinanceApiClient $yahooFinanceClientApi
         */
        $yahooFinanceClientApi = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        $response = $yahooFinanceClientApi->fetchStockProfile('AMZN', 'US');

        $stockProfile = json_decode($response['content']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('USD', $stockProfile->currency);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $stockProfile->region);
        $this->assertIsFloat( $stockProfile->previousClose);
        $this->assertIsFloat($stockProfile->price);
        $this->assertIsFloat($stockProfile->priceChange);
    }
}