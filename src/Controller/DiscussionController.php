<?php
namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class DiscussionController extends AbstractController
{
    #[Route('/discussion/{id}', name: 'app_discussion')]
    public function index(
        Members $member,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $currentUser = $this->getUser(); // the logged-in member

        // Fetch all members to display in the sidebar
        $members = $em->getRepository(Members::class)->findAll();

        // Create a new message if the form was submitted
        if ($request->isMethod('POST')) {
            // 🚫 Ban check for logged-in user
            if (
                $currentUser instanceof Members &&
                $currentUser->getIsBanned() &&
                (
                    $currentUser->getBanUntil() === null ||
                    $currentUser->getBanUntil() > new \DateTime()
                )
            ) {
                $this->addFlash('error', 'You are banned and cannot send messages.');
                return $this->redirectToRoute('app_discussion', ['id' => $member->getId()]);
            }

            $message = trim($request->request->get('message'));
            $image = $request->files->get('image'); // Handle the image upload

            if (!empty($message) || $image) {
                $discussion = new Discussion();
                $discussion->setSender($currentUser);
                $discussion->setReceiver($member);
                $discussion->setMessage($message);
                $discussion->setCreatedAt(new \DateTimeImmutable());

                // Handle image upload if present
                if ($image instanceof UploadedFile) {
                    // Generate a unique file name
                    $imageFilename = uniqid() . '.' . $image->guessExtension();

                    // Move the uploaded image to the 'public/uploads/messages' folder
                    try {
                        $image->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/messages',
                            $imageFilename
                        );
                    } catch (FileException $e) {
                        // Handle exception if something goes wrong
                        $this->addFlash('error', 'Unable to upload image');
                        return $this->redirectToRoute('app_discussion', ['id' => $member->getId()]);
                    }

                    // Save the image filename in the discussion
                    $discussion->setImage($imageFilename);
                }

                // Persist the message in the database
                $em->persist($discussion);
                $em->flush();
            }

            // Redirect back to the discussion page
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

        // Render the template with discussions, member data, and all members
        return $this->render('discussion/index.html.twig', [
            'member' => $member,
            'discussions' => $discussions,
            'members' => $members, // Pass the members here
        ]);
    }
}
