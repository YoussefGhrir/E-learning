<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email existe déjà
            if ($entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])) {
                $this->addFlash('error', 'Un compte avec cet email existe déjà.');
                return $this->redirectToRoute('app_register');
            }

            // Hacher le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            // Définir les rôles
            $selectedRole = $form->get('roles')->getData();
            $allowedRoles = ['ROLE_ADMIN', 'ROLE_STUDENT', 'ROLE_TEACHER', 'ROLE_PARTNER'];
            $user->setRoles(in_array($selectedRole, $allowedRoles) ? [$selectedRole, 'ROLE_USER'] : ['ROLE_USER']);

            // L'utilisateur est directement activé sans vérification d'email
            $user->setIsVerified(true);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès. Vous pouvez vous connecter immédiatement.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
