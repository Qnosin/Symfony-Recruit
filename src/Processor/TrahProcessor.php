<?php

namespace App\Processor;

class TrahProcessor
{

    public function transform(array $row): ?array
    {

        if ($row[1] === ">10") {
            $row[1] = 11;
        }

        if ($row[5] === 'NARZEDZIA WARSZTAT') {
            return null;
        }

        return [
            'externalId' => $row[0],
            'mpn' => $row[3],
            'name' => '',
            'producerName' => $row[5],
            'quantity' => (int)$row[1],
            'price' => (double)$row[2],
            'ean' => $row[4],
        ];
    }

}