<?php

namespace App\Controller;
use App\Repository\CategoryRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Poste;
use App\Entity\Comment;
use App\Form\PosteType;
use App\Repository\PosteRepository;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/poste')]
final class PosteController extends AbstractController{
    #[Route(name: 'app_poste_index', methods: ['GET'])]
    public function index(PosteRepository $posteRepository, LikeRepository $likeRepository): Response
{
    $postes = $posteRepository->findAll();
    $user = $this->getUser();
    $likedPostes = [];

    if ($user) {
        foreach ($postes as $poste) {
            foreach ($poste->getLikes() as $like) {
                if ($like->getUser() === $user) {
                    $likedPostes[] = $poste->getId();
                    break;
                }
            }
        }
    }

    // Debugging
    //dump($likedPostes); die;

    return $this->render('poste/index.html.twig', [
        'postes' => $postes,
        'likedPostes' => $likedPostes,
        'user' => $user,
    ]);
}
    #[Route('/comment/add/{id}', name: 'add_comment', methods: ['POST'])]
    public function addComment(Request $request, Poste $poste, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Comment content cannot be empty'], 400);
        }

        $comment = new Comment();
        $comment->setPoste($poste);
        $comment->setUser($this->getUser());
        $comment->setContenu($data['content']);
        $comment->setCreatedAt(new \DateTime());

        $entityManager->persist($comment);
        $entityManager->flush();

        $profile = $this->getUser()->getProfile();

        return new JsonResponse([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'content' => $comment->getContenu(),
                'user' => [
                    'id' => $this->getUser()->getId(),
                    'firstName' => $profile ? $profile->getFirstName() : 'Anonymous',
                    'lastName' => $profile ? $profile->getLastName() : 'User',
                    'profilePicture' => $profile ? $profile->getProfilePicture() : null,
                ],
            ],
        ]);
    }
    #[Route('/new', name: 'app_poste_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        CategoryRepository $categoryRepository
    ): Response {
        $user = $this->getUser();
        $poste = new Poste();
        $form = $this->createForm(PosteType::class, $poste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                $poste->setImage($newFilename);
            }

            // Associer les catégories sélectionnées au poste
            $selectedCategories = $form->get('categories')->getData();
            foreach ($selectedCategories as $category) {
                $poste->addCategory($category);
            }

            $poste->setUser($user);
            $entityManager->persist($poste);
            $entityManager->flush();

            return $this->redirectToRoute('app_poste_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('poste/new.html.twig', [
            'poste' => $poste,
            'form' => $form,
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_poste_show', methods: ['GET'])]
    public function show(Poste $poste): Response
    {
        return $this->render('poste/show.html.twig', [
            'poste' => $poste,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_poste_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Poste $poste,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository // Injectez CategoryRepository
    ): Response {
        $form = $this->createForm(PosteType::class, $poste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer les catégories sélectionnées au poste
            $selectedCategories = $form->get('categories')->getData();
            foreach ($selectedCategories as $category) {
                $poste->addCategory($category);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_poste_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('poste/edit.html.twig', [
            'poste' => $poste,
            'form' => $form,
            'categories' => $categoryRepository->findAll(), // Passez les catégories au template
        ]);
    }
    #[Route('/{id}', name: 'app_poste_delete', methods: ['POST'])]
    public function delete(Request $request, Poste $poste, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$poste->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($poste);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_poste_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/comment/edit/{id}', name: 'edit_comment', methods: ['POST'])]
    public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier si l'utilisateur actuel est le propriétaire du commentaire
        if ($comment->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'You are not authorized to edit this comment.'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Comment content cannot be empty'], 400);
        }

        // Mettre à jour le contenu du commentaire
        $comment->setContenu($data['content']);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'content' => $comment->getContenu(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i'), // Ajout de la date de création
            ],
        ]);
    }

    #[Route('/comment/delete/{id}', name: 'delete_comment', methods: ['POST'])]
    public function deleteComment(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier si l'utilisateur actuel est le propriétaire du commentaire
        if ($comment->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'You are not authorized to delete this comment.'], 403);
        }

        // Supprimer le commentaire
        $entityManager->remove($comment);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
    #[Route('/comments/{id}', name: 'get_comments', methods: ['GET'])]
    public function getComments(Poste $poste): JsonResponse
    {
        $comments = [];

        foreach ($poste->getComments() as $comment) {
            $user = $comment->getUser();
            $profile = $user->getProfile(); // Récupérer le profil de l'utilisateur

            $comments[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContenu(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'), // Ajout de la date de création
                'user' => [
                    'id' => $user->getId(),
                    'firstName' => $profile ? $profile->getFirstName() : 'Anonymous',
                    'lastName' => $profile ? $profile->getLastName() : 'User',
                    'profilePicture' => $profile && $profile->getProfilePicture()
                        ? $this->getParameter('profile_pictures_directory') . '/' . $profile->getProfilePicture()
                        : null, // Chemin complet de l'image de profil
                ],
            ];
        }

        return $this->json(['comments' => $comments]);
    }
}
