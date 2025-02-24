<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Poste;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/like')]
class LikeController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'app_like_toggle', methods: ['POST'])]
    public function toggleLike(Poste $poste, EntityManagerInterface $em, LikeRepository $likeRepo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        // Vérifier si l'utilisateur a déjà liké ce poste
        $like = $likeRepo->findOneBy(['user' => $user, 'poste' => $poste]);

        if ($like) {
            $em->remove($like); // Supprimer le like
            $liked = false;
        } else {
            $like = new Like();
            $like->setUser($user);
            $like->setPoste($poste);
            $em->persist($like); // Ajouter un nouveau like
            $liked = true;
        }

        $em->flush();

        // Retourner une réponse JSON avec le nombre de likes mis à jour
        return new JsonResponse([
            'success' => true,
            'liked' => $liked,
            'likesCount' => $poste->getLikes()->count(), // Nombre de likes mis à jour
        ]);
    }
#[Route('/count/{id}', name: 'app_like_count', methods: ['GET'])]
public function getLikeCount(Poste $poste): JsonResponse
{
    return new JsonResponse(['likeCount' => $poste->getLikes()->count()]);
}


}