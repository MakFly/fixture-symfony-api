<?php

namespace App\Controller;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $items = $em->getRepository(Item::class)->findBy([], ['id' => 'ASC']);

        return $this->render('items/index.html.twig', [
            'items' => $items,
            'buildId' => $_ENV['PLOYDOK_BUILD_ID'] ?? getenv('PLOYDOK_BUILD_ID') ?: 'unknown',
        ]);
    }

    #[Route('/items', name: 'item_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $name = trim((string) $request->request->get('name', ''));

        if ($name !== '' && mb_strlen($name) <= 255) {
            $item = new Item();
            $item->name = $name;
            $em->persist($item);
            $em->flush();
        }

        return $this->redirectToRoute('home');
    }
}
