<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class OffersController extends AbstractController
{
    #[IsGranted('ROLE_PARTNER')]
    #[Route('/offers', name: 'app_offers')]
    public function index(): Response
    {
        return $this->render('offers/index.html.twig', [
            'controller_name' => 'OffersController',
        ]);
    }
    #[Route('/offers_dashboard', name: 'app_offers1')]
    public function index1(): Response
    {
        return $this->render('offers/index1.html.twig', [
            'controller_name' => 'OffersController',
        ]);
    }
}
