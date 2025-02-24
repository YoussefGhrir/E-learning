<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(): Response
    {
        return $this->render('events/index.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }
    #[Route('/event_dashboard', name: 'app_events1')]
    public function index1(): Response
    {
        return $this->render('events/index1.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }
}
