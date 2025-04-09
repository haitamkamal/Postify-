<?php

// src/Controller/ProfileController.php

// src/Controller/ProfileController.php

namespace App\Controller;

use App\Form\ProfileImageType;
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

    // Inject the EntityManagerInterface into the controller
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
            // If no user is logged in, redirect to the login page
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileImageType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('profile_image')->getData();

            if ($file) {
                // Generate a unique filename for the image
                $filename = uniqid() . '.' . $file->guessExtension();

                try {
                    // Move the file to the 'uploads/profile_images' directory
                    $file->move(
                        $this->getParameter('profile_images_directory'),  // This will be defined in services.yaml
                        $filename
                    );

                    // Update the user's profile image in the database
                    $user->setProfileImage($filename);
                    $this->entityManager->flush();  // Persist the changes

                    $this->addFlash('success', 'Profile image updated successfully!');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image!');
                }
            }

            return $this->redirectToRoute('profile');
        }

        // Get the profile image filename (handle null case)
        $profileImageFilename = $user->getProfileImage() ? 'uploads/profile_images/' . $user->getProfileImage() : 'uploads/profile_images/default_image.png';

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
            'profileImage' => $profileImageFilename,
        ]);
    }
}
