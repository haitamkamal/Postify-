<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DiscussionController extends AbstractController
{
    #[Route('/discussion/{id}', name: 'app_discussion')]
    public function index(
        Members $member,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $currentUser = $this->getUser(); // the logged-in member

        // Create a new message if the form was submitted
        if ($request->isMethod('POST')) {
            $message = trim($request->request->get('message'));
            if (!empty($message)) {
                $discussion = new Discussion();
                $discussion->setSender($currentUser);
                $discussion->setReceiver($member);
                $discussion->setMessage($message);
                $discussion->setCreatedAt(new \DateTimeImmutable());

                $em->persist($discussion);
                $em->flush();
            }

            return $this->redirectToRoute('app_discussion', ['id' => $member->getId()]);
        }

        // Fetch all messages between current user and the selected member
        $repo = $em->getRepository(Discussion::class);
        $discussions = $repo->createQueryBuilder('d')
            ->where('(d.sender = :me AND d.receiver = :them) OR (d.sender = :them AND d.receiver = :me)')
            ->setParameter('me', $currentUser)
            ->setParameter('them', $member)
            ->orderBy('d.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('discussion/index.html.twig', [
            'member' => $member,
            'discussions' => $discussions,
        ]);
    }
}
