<?php


namespace App\Http;


use Symfony\Component\HttpFoundation\JsonResponse;

interface YahooFinanceApiClientInterface
{
    public function fetchStockProfile(string $symbol, string $region): JsonResponse;
}