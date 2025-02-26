<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Poste;
use App\Repository\MessageRepository;
use App\Repository\PosteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/forumdashboard')]
class BackController extends AbstractController
{
    // Gestion des messages signalés
    #[Route('/messages', name: 'forumdashboard_messages', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function reportedMessages(MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findBy(['signaled' => true], ['createdAt' => 'ASC']);
        return $this->render('back/messages.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/messages/unreport/{id}', name: 'forum_unreport_message', methods: ['GET'])]
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

        return $this->redirectToRoute('forumdashboard_messages');
    }

    #[Route('/messages/delete/{id}', name: 'forumdashboard_delete_message', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteMessage(Message $message, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($message);
        $entityManager->flush();

        $this->addFlash('success', 'Message deleted successfully.');
        return $this->redirectToRoute('forumdashboard_messages');
    }

    // Gestion des postes signalés
    #[Route('/postes', name: 'forumdashboard_postes', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function reportedPostes(PosteRepository $posteRepository): Response
    {
        $postes = $posteRepository->findBy(['signaled' => true]);
        return $this->render('back/postes.html.twig', [
            'postes' => $postes,
        ]);
    }

    #[Route('/postes/unreport/{id}', name: 'forum_unreport_poste', methods: ['GET'])]
    public function unreportPoste(PosteRepository $posteRepository, EntityManagerInterface $entityManager, int $id): RedirectResponse
    {
        $poste = $posteRepository->find($id);

        if (!$poste) {
            $this->addFlash('error', 'Poste not found.');
        } else {
            $poste->setSignaled(false);
            $entityManager->flush();
            $this->addFlash('success', 'Poste unreported successfully.');
        }

        return $this->redirectToRoute('forumdashboard_postes');
    }

    #[Route('/postes/delete/{id}', name: 'forumdashboard_delete_poste', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deletePoste(Poste $poste, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($poste);
        $entityManager->flush();

        $this->addFlash('success', 'Poste deleted successfully.');
        return $this->redirectToRoute('forumdashboard_postes');
    }
}
