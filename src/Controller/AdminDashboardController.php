<?php
// src/Controller/AdminController.php
namespace App\Controller;

use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    
    #[Route("/admin_dashboard", name:"admin_dashboard")] 
    public function dashboard(EntityManagerInterface $em)
    {
        // Only admins can access this page
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get all users
    $members = $em->getRepository(Members::class)->findAll();

        return $this->render('admin_dashboard/index.html.twig', [
            'users' => $members
        ]);
    }

    
    #[Route("/admin/ban/{id}", name:"admin_ban_user")]
     
    public function banUser(Members $user, EntityManagerInterface $em)
    {
        $banDuration = 10; // Duration in minutes
        $banUntil = new \DateTime();
        $banUntil->add(new \DateInterval("PT{$banDuration}M"));

        $user->setIsBanned(true);
        $user->setBanUntil($banUntil);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'User has been banned for 10 minutes.');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/unban/{id}', name: 'admin_unban')]
        public function unbanUser(Members $member, EntityManagerInterface $em): Response
        {
            $member->setIsBanned(false);
            $member->setBanUntil(null);
            $em->flush();

            $this->addFlash('success', $member->getUsername() . ' has been unbanned.');
            return $this->redirectToRoute('admin_dashboard'); 
        }

    
#[Route('/admin/delete/{id}', name: 'admin_delete_user')]
public function delete(Members $member, EntityManagerInterface $em, Request $request): Response
{
    if (!$this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
        throw $this->createAccessDeniedException('Invalid CSRF token.');
    }

    if (!$member->getDiscussions()->isEmpty()) {
        $this->addFlash('error', 'Impossible de supprimer un membre avec des discussions actives.');
        return $this->redirectToRoute('admin_dashboard');
    }

    $em->remove($member);
    $em->flush();

    $this->addFlash('success', 'Member deleted successfully.');
    return $this->redirectToRoute('admin_dashboard');
}


    
    #[Route("/admin/edit/{id}", name:"admin_edit_user")]
    
    public function editUser(Members $user, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Handle the form submission to edit the user profile
        if ($request->isMethod('POST')) {
            $user->setUsername($request->get('username')); // Example: Update username

            // If you have fields for profile image, email, etc., you can update them here
            if ($request->get('profileImage')) {
                $user->setProfileImage($request->get('profileImage'));
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User profile has been updated.');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin_dashboard/edit_user.html.twig', [
            'user' => $user,
        ]);
    }
}
