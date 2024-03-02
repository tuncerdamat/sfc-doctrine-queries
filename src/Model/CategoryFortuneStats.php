<?php

namespace App\Model;

class CategoryFortuneStats
{
    public function __construct(
        public int $fortunesPrinted,
        public float $fortunesAverage,
        public string $categoryName,
    )
    {
    }
}