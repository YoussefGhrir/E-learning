<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ProfileRepository $profileRepository, UserRepository $userRepository): Response
    {
        $profiles = $profileRepository->findAll();
        $users = $userRepository->findAll();
        $user = $this->getUser();
        $profile = $user ? $user->getProfile() : null;
    
        return $this->render('profile/index.html.twig', [
            'profiles' => $profiles,  // ✅ Pass all profiles
            'users' => $users,        // ✅ Pass all users (if needed)
            'profile' => $profile,    // ✅ Pass current user's profile
        ]);
    }
    


    #[Route('/{id<\d+>}', name: 'app_profile_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(ProfileRepository $profileRepository, int $id): Response
    {
        $user = $this->getUser();
        $profile = $profileRepository->find($id);
        
        if (!$profile) {
            throw $this->createNotFoundException('Profile not found.');
        }

        if ($user !== $profile->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You can only view your own profile.');
        }

        return $this->render('profile/show.html.twig', [
            'profile' => $profile,
        ]);
    }

    #[Route('/new', name: 'app_profile_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user->getProfile()) {
            $this->addFlash('error', 'You already have a profile!');
            return $this->redirectToRoute('app_profile_show', ['id' => $user->getProfile()->getId()]);
        }

        $profile = new Profile();
        $profile->setUser($user);

        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($profile);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_show', ['id' => $profile->getId()]);
        }

        return $this->render('profile/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function edit(Request $request, Profile $profile, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    if ($user !== $profile->getUser() && !$this->isGranted('ROLE_ADMIN')) {
        throw $this->createAccessDeniedException('You can only edit your own profile.');
    }

    $form = $this->createForm(ProfileType::class, $profile);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('profilePicture')->getData();
        
        if ($imageFile) {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move($uploadDir, $newFilename);

                // Delete old image if it exists
                if ($profile->getProfilePicture()) {
                    $oldFile = $uploadDir . '/' . basename($profile->getProfilePicture());
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Save new file path (relative to `/public`)
                $profile->setProfilePicture('uploads/profiles/' . $newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Error uploading new profile picture.');
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Profile updated successfully!');

        return $this->redirectToRoute('app_profile_show', ['id' => $profile->getId()]);
    }

    return $this->render('profile/edit.html.twig', [
        'profile' => $profile, // ✅ Ensure profile is passed
        'form' => $form->createView(),
    ]);
}

    #[Route('/{id}', name: 'app_profile_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Request $request, Profile $profile, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user !== $profile->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You can only delete your own profile.');
        }

        if ($this->isCsrfTokenValid('delete' . $profile->getId(), $request->request->get('_token'))) {
            if ($profile->getProfilePicture()) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                $filePath = $uploadDir . '/' . $profile->getProfilePicture();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $entityManager->remove($profile);
            $entityManager->flush();
            $this->addFlash('success', 'Profile deleted successfully!');
        }

        return $this->redirectToRoute('app_profile_index');
    }
}
