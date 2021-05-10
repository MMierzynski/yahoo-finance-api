<?php


namespace App\Tests\Feature;


use App\Entity\Stock;
use App\Http\FakeYahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;
use App\Tests\DatabasePrimer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{
    public function testRefreshStockProfileCommandBehavesCorrectlyWhenStockRecordDoesNotExists()
    {
        // setup
        $application = new Application(self::$kernel);

        // command
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD","price":3258.7083,"previousClose":3172.69,"priceChange":86.02}';

        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $stockRepository = $this->entityManager->getRepository(Stock::class);

        $stock = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50, $stock->getPreviousClose());
        $this->assertGreaterThan(50, $stock->getPrice());
    }

    public function testNon200StatusCodeResponsesAreHandledCorrectly()
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$statusCode = 500;
        FakeYahooFinanceApiClient::$content = 'Finance API Client Error ';

        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $stockRepository = $this->entityManager->getRepository(Stock::class);
        $stockRecordCount = $stockRepository->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(1, $commandStatus);

        $this->assertEquals(0, $stockRecordCount);
    }
}