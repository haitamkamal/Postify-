<?php

namespace App\Controller;

use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LogInController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils,EntityManagerInterface  $em): Response
    {
          if ($this->getUser()) {
                    // Always get FRESH user data from database
                    $freshUser = $em->getRepository(Members::class)->find($this->getUser()->getId());
        
                    // Debugging - check what roles are actually loaded
                    dump($freshUser->getRoles());
                    
                    // Check roles on the fresh user object
                    if (in_array('ROLE_ADMIN', $freshUser->getRoles())) {
                        return $this->redirectToRoute('app_admin');
                    }
                    return $this->redirectToRoute('app_user');
                }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
