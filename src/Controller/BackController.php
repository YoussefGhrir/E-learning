<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/forumdashboard')]
class BackController extends AbstractController
{
    #[Route('/', name: 'forumdashboard_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')] // Restrict access to admins
    public function index(MessageRepository $messageRepository): Response
    {
        
        $messages  = $messageRepository->findBy(['signaled' => true], ['createdAt' => 'ASC']);

        return $this->render('back/index.html.twig', [
            'messages' => $messages,
        ]);
    }
    #[Route('/unreport/{id}', name: 'forum_unreport_message', methods: ['GET'])]
public function unreportMessage(MessageRepository $messageRepository, EntityManagerInterface $entityManager, int $id): RedirectResponse
{
    $message = $messageRepository->find($id);

    if (!$message) {
        $this->addFlash('error', 'Message not found.');
    } else {
        $message->setSignaled(false);
        $entityManager->flush();
        $this->addFlash('success', 'Message unreported successfully.');
    }

    return $this->redirectToRoute('forumdashboard_index'); // Redirect to the dashboard
}
    #[Route('/delete/{id}', name: 'forumdashboard_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')] 
    public function delete(Message $message, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($message);
        $entityManager->flush();

        $this->addFlash('success', 'Message deleted successfully.');
        return $this->redirectToRoute('forumdashboard_index');
    }
}
