<?php

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
        $user = $this->getUser();  // Get the currently logged-in user
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

        // Get the profile image filename
        $profileImageFilename = 'uploads/profile_images/' . $user->getProfileImage();
        
        // Check if the file exists and get the modification time for cache-busting
        $profileImageTimestamp = file_exists($profileImageFilename) ? filemtime($profileImageFilename) : time();

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
            'profileImageTimestamp' => $profileImageTimestamp, // Pass timestamp to Twig for cache-busting
        ]);
    }
}
