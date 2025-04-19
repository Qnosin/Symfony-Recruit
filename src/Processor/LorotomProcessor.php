<?php

namespace App\Processor;

class LorotomProcessor
{
    public function transform(array $row): array
    {
        if ((int) $row['quantity'] > 30) {
            $row['quantity'] = 31;
        }

        return [
            'externalId' => $row['our_code'],
            'mpn' => $row['producer_code'],
            'name' => $row['name'],
            'producerName' => $row['producer'],
            'quantity' => (int)$row['quantity'],
            'price' => (double)$row['price'],
            'ean' => $row['ean'],
        ];
    }

}