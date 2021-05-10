<?php

namespace App\Tests;

use App\Entity\Stock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StockTest extends DatabaseDependantTestCase
{
    public function testStockRecordCanBeCreatedInTheDatabase()
    {
        //set-up
        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setShortName('Amazon Inc');
        $stock->setCurrency('USD');
        $stock->setExchangeName('Nasdaq');
        $stock->setRegion('US');
        $price = 1000;
        $previousPrice = 1100;
        $priceChange = $price - $previousPrice;
        $stock->setPrice($price);
        $stock->setPreviousClose($previousPrice);
        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);

        // do something
        $this->entityManager->flush();

        $stockRepository = $this->entityManager->getRepository(Stock::class);

        $stockRecord = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        //assert
        $this->assertEquals('Amazon Inc', $stockRecord->getShortName());
        $this->assertEquals('USD', $stockRecord->getCurrency());
        $this->assertEquals('Nasdaq', $stockRecord->getExchangeName());
        $this->assertEquals('US', $stockRecord->getRegion());
        $this->assertEquals(1000, $stockRecord->getPrice());
        $this->assertEquals(1100, $stockRecord->getPreviousClose());
        $this->assertEquals(-100, $stockRecord->getPriceChange());
    }
}
