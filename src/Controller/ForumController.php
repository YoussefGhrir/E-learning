<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/forum')]
class ForumController extends AbstractController
{
    #[Route('/', name: 'forum_index', methods: ['GET'])]
    public function index(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $messages = $messageRepository->findBy([], ['createdAt' => 'ASC']);

        return $this->render('forum/index.html.twig', [
            'messages' => $messages,
            'user' =>$user,
        ]);
    }

    #[Route('/messages', name: 'forum_get_messages', methods: ['GET'])]
    public function getMessages(MessageRepository $messageRepository): JsonResponse
    {
        $messages = $messageRepository->findBy([], ['createdAt' => 'ASC']);

        $formattedMessages = array_map(function ($message) {
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'user' => $message->getUser()->getEmail(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i'),
            ];
        }, $messages);

        return $this->json(['messages' => $formattedMessages]);
    }
    
    #[Route('/report/{id}', name: 'forumdashboard_report', methods: ['POST'])]
    public function report(Message $message, EntityManagerInterface $entityManager): JsonResponse
    {
        $message->setSignaled(true);
        $entityManager->flush();
    
        return $this->json(['success' => true]);
    }
    #[Route('/add', name: 'forum_add_message', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addMessage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['content']) || empty(trim($data['content']))) {
            return $this->json(['success' => false, 'message' => 'Message cannot be empty'], 400);
        }

        $message = new Message();
        $message->setContent($data['content']);
        $message->setUser($this->getUser());
        

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'user' => $message->getUser()->getEmail(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i'),
            ]
        ]);
    }
    #[Route('/delete/{id}', name: 'forum_delete_message', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteMessage(Message $message, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        // Autoriser la suppression si l'utilisateur est le propriétaire du message OU s'il est administrateur
        if ($message->getUser()->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Supprimer le message
        $entityManager->remove($message);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Message deleted successfully']);
    }
}
