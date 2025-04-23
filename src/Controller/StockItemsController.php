<?php

namespace App\Controller;

use App\Entity\StockItem;
use App\Repository\StockItemRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api', name: 'api_')]
class StockItemsController extends AbstractController
{
    #[Route('/get-stocks', name: 'api_get_stocks')]
    public function index(  Request $request, StockItemRepository $item_repo): JsonResponse
    {
        $mpn = $request->query->get('mpn');
        $ean = $request->query->get('ean');

        if (!$mpn && !$ean) {
            throw new BadRequestHttpException('You must provide at least "mpn" or "ean" as a query parameter.');
        }
        $items = $item_repo->findBy(array_filter([
            'mpn' => $mpn,
            'ean' => $ean,
        ]));


        if ($mpn && !$ean) {
            $items = $item_repo->findBy(['mpn' => $mpn]);
        } elseif (!$mpn && $ean) {
            $items = $item_repo->findBy(['ean' => $ean]);
        }

        return $this->json($items,status: 200,headers:["Content-Type' =>'application/json"]);
    }
}
