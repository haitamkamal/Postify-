<?php
namespace App\Controller;

use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MemberController extends AbstractController
{
    // List all members (available to authenticated users and admins)
    #[Route('/members', name: 'member_list')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure only authenticated members can access
    public function listMembers(EntityManagerInterface $entityManager): Response
    {
        // Fetch all members
        $repository = $entityManager->getRepository(Members::class);
        $members = $repository->findAll();

        return $this->render('member/list.html.twig', [
            'members' => $members,
        ]);
    }
        // View a single member's profile
    #[Route('/member/{id}', name: 'member_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure only authenticated members can access
    public function viewMember($id, EntityManagerInterface $entityManager): Response
    {
        // Fetch the member from the database
        $member = $entityManager->getRepository(Members::class)->find($id);

        // If the member doesn't exist, throw a 404 error
        if (!$member) {
            throw $this->createNotFoundException('Member not found');
        }

        // Render the member profile page
        return $this->render('member/profile.html.twig', [
            'member' => $member,
        ]);
    }

}
