<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation')]
    public function index(): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'controller_name' => 'ReclamationController',
        ]);
    }
    #[Route('/reclamation_dashboard', name: 'app_reclamation1')]
    public function index1(): Response
    {
        return $this->render('reclamation/index1.html.twig', [
            'controller_name' => 'ReclamationController',
        ]);
    }
}
