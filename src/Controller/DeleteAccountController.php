<?php
// src/Controller/DeleteAccountController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DeleteAccountController extends AbstractController
{
    private $entityManager;
    private $tokenStorage;

    // Inject EntityManager and TokenStorageInterface into the controller
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/profile/delete', name: 'delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request): Response
    {
        // Get the currently authenticated user
        $user = $this->getUser();
        
        if (!$user) {
            // Redirect to login if the user is not authenticated
            return $this->redirectToRoute('app_login');
        }

        // Validate the CSRF token
        if ($this->isCsrfTokenValid('delete_account', $request->get('_token'))) {
            // Remove the user from the database
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            // Log out the user after deleting the account
            $this->tokenStorage->setToken(null);  // Clear the authentication token
            $this->addFlash('success', 'Your account has been deleted.');

            return $this->redirectToRoute('app_home');  // Redirect to home page or login page
        }

        // If CSRF is invalid, show an error and redirect back to the profile page
        $this->addFlash('error', 'An error occurred.');
        return $this->redirectToRoute('profile');
    }
}
