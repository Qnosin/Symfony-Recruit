<?php

namespace App\Tests\functional;

use App\Entity\StockItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class StockApiControllerTest extends WebTestCase
{

    private function createStockItem(EntityManagerInterface $entityManager, string $mpn, string $ean): StockItem
    {

        $existingStockItem = $entityManager->getRepository(StockItem::class)->findOneBy([
            'mpn' => $mpn,
            'ean' => $ean,
        ]);

        if ($existingStockItem) {
            return $existingStockItem;
        }else{
            $stockItem = new StockItem();
            $stockItem->setMpn($mpn);
            $stockItem->setEan($ean);
            $stockItem->setQuantity(10);
            $stockItem->setPrice(100);
            $stockItem->setExternalId("000 013");

            $entityManager->persist($stockItem);
            $entityManager->flush();

            return $stockItem;

        }

    }

    public function testGetStocksWithMpn(){

        dump($_ENV['APP_ENV']);
        dump($_ENV['DATABASE_URL']);

        $client = static::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->createStockItem($entityManager,'19-598', '7612720201662');

        $client->request('GET', '/api/get-stocks?mpn=19-598');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $responseData);
        $this->assertEquals('19-598', $responseData[0]['mpn']);
    }

    public function testGetStocksWithMpnAndEan()
    {
        $client = static::createClient();



        $client->request('GET', '/api/get-stocks?mpn=19-598&ean=7612720201662');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->createStockItem($entityManager,'19-598', '7612720201662');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $responseData);
        $this->assertEquals('19-598', $responseData[0]['mpn']);
        $this->assertEquals('7612720201662', $responseData[0]['ean']);
    }


    public function testGetStocksWithoutParameters()
    {

        $client = static::createClient();
        $client->request('GET', '/api/get-stocks');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertNull($responseData);

    }

}