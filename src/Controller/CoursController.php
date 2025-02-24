<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CoursController extends AbstractController
{
    #[Route('/cours', name: 'app_cours')]
    #[IsGranted('ROLE_TEACHER')]

    public function index(): Response
    {
        return $this->render('cours/index.html.twig', [
            'controller_name' => 'CoursController',
        ]);
    }
    #[Route('/cours_dashboard', name: 'app_cours1')]
    public function index1(): Response
    {
        return $this->render('cours/index1.html.twig', [
            'controller_name' => 'CoursController',
        ]);
    }
    
}
