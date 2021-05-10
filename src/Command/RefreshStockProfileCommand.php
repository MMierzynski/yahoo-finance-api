<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\YahooFinanceApiClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';
    protected static $defaultDescription = 'Add a short description for your command';
    private EntityManagerInterface $entityManager;
    private YahooFinanceApiClientInterface $yahooFinanceApiClient;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, YahooFinanceApiClientInterface $yahooFinanceApiClient, SerializerInterface $serializer)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->yahooFinanceApiClient = $yahooFinanceApiClient;
        $this->serializer = $serializer;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('symbol', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('region', InputArgument::REQUIRED, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stockProfile = $this->yahooFinanceApiClient->fetchStockProfile(
            $input->getArgument('symbol'),
            $input->getArgument('region')
        );

        if (200 !== $stockProfile->getStatusCode()){
            return Command::FAILURE;
        }

        $stock = $this->serializer->deserialize($stockProfile->getContent(), Stock::class, 'json');

        /*$priceChange = $stockProfile->price - $stockProfile->previousClose;
        $stock = new Stock();
        $stock->setCurrency($stockProfile->currency)
            ->setExchangeName($stockProfile->exchangeName)
            ->setPreviousClose($stockProfile->previousClose)
            ->setPrice($stockProfile->price)
            ->setPriceChange($priceChange)
            ->setRegion($stockProfile->region)
            ->setShortName($stockProfile->shortName)
            ->setSymbol($stockProfile->symbol);*/

        $this->entityManager->persist($stock);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
