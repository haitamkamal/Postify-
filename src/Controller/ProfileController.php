<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Form\ProfileImageType;
use App\Form\GenderType;
use App\Entity\Members;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class ProfileController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/profile', name: 'profile')]
    public function profile(Request $request): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();

        // Check if user is logged in
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Create forms for profile image and gender
        $formImage = $this->createForm(ProfileImageType::class, $user);
        $formImage->handleRequest($request);

        $formGender = $this->createForm(GenderType::class, $user);
        $formGender->handleRequest($request);

        // Handling profile image update
        if ($formImage->isSubmitted() && $formImage->isValid()) {
            $file = $formImage->get('profile_image')->getData();

            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('profile_images_directory'),
                        $filename
                    );

                    $user->setProfileImage($filename);
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Profile image updated successfully!');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image!');
                }
            }

            return $this->redirectToRoute('profile');
        }

        // Handling gender update
        if ($formGender->isSubmitted() && $formGender->isValid()) {
            $this->entityManager->flush();  // Persist the gender changes

            $this->addFlash('success', 'Your gender has been updated!');
            return $this->redirectToRoute('profile');
        }

        // Get the profile image filename
        $profileImageFilename = $user->getProfileImage() ? 'uploads/profile_images/' . $user->getProfileImage() : 'uploads/profile_images/default_image.png';

        return $this->render('profile/index.html.twig', [
            'form' => $formImage->createView(),       // Make sure 'form' is passed
            'formGender' => $formGender->createView(), // And so is 'formGender'
            'profileImage' => $profileImageFilename,
        ]);
    }
}
