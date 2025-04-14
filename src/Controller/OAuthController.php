<?php
namespace App\Controller;

use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class OAuthController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google')]
    public function connectGoogle(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('google')->redirect();
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheck(Request $request, ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $client = $clientRegistry->getClient('google');
        $googleUser = $client->fetchUser();

        $email = $googleUser->getEmail();
        $user = $em->getRepository(Members::class)->findOneBy(['email' => $email]);

  if (!$user) {
        $user = new Members();
        $user->setEmail($email);
        $user->setUsername($googleUser->getName());
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(''); // optional if using OAuth only

        // Set the gender if available
        $gender = $googleUser->toArray()['gender'] ?? null;
        if ($gender) {
            $user->setGender($gender);
            
        }

        $em->persist($user);
        $em->flush();
    }


        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        return $this->redirectToRoute('app_home');
    }

    #[Route('/connect/github', name: 'connect_github')]
    public function connectGithub(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('github')
        ->redirect(['user:email']);
    }

    #[Route('/connect/github/check', name: 'connect_github_check')]
    public function connectGithubCheck(Request $request, ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $client = $clientRegistry->getClient('github');
        $githubUser = $client->fetchUser();

        $email = $githubUser->getEmail();
        $user = $em->getRepository(Members::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new Members();
            $user->setEmail($email);
            $user->setUsername($githubUser->getNickname() ?? $githubUser->getName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('');

            $user->setGender(null);

            $em->persist($user);
            $em->flush();
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        return $this->redirectToRoute('app_home');
    }
}
