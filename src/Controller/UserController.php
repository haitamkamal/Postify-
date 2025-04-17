<?php

namespace App\Controller;

use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(EntityManagerInterface $entityManager): Response
    {   
        // Fetch all members
        $members = $entityManager->getRepository(Members::class)->findAll();

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
             'members' => $members, 
        ]);
    }
}
