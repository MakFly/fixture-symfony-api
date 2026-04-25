<?php

namespace App\Controller;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ItemController
{
    #[Route('/api/items', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $items = $em->getRepository(Item::class)->findBy([], ['id' => 'ASC']);

        return new JsonResponse(array_map(
            fn (Item $i) => ['id' => $i->id, 'name' => $i->name],
            $items
        ));
    }
}
