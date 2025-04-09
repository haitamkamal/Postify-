<?php

namespace App\Controller;

use App\Entity\Members;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdminUpgradeController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/upgrade-role', name: 'upgrade_to_admin', methods: ['GET', 'POST'])]
public function upgradeToAdmin(
    Request $request,
    EntityManagerInterface $entityManager,
    TokenStorageInterface $tokenStorage
): Response {
    // 1. Verify user is authenticated
    $user = $this->getUser();
    if (!$user instanceof Members) {
        $this->addFlash('error', 'You need to be logged in to upgrade your role.');
        return $this->redirectToRoute('app_login');
    }

    // 2. Handle form submission
    if ($request->isMethod('POST')) {
        $adminPassword = $request->request->get('admin_password');
        
        if (!$adminPassword) {
            $this->addFlash('error', 'Please enter the admin password.');
            return $this->redirectToRoute('upgrade_to_admin');
        }

        // 3. Verify admin password
        if ($this->verifyAdminPassword($adminPassword)) {
            if (!$user->hasRole('ROLE_ADMIN')) {
                try {
                    // 4. Upgrade user roles
                    $user->setRoles(['ROLE_ADMIN']); // Upgrade to admin
                    $entityManager->flush();

                    // 5. Refresh the user from the database to ensure we have the latest data
                    $entityManager->refresh($user);

                    // 6. Create a new authenticated token with the updated roles
                    $token = new UsernamePasswordToken(
                        $user,
                        'main', // The firewall name (check security.yaml)
                        $user->getRoles()
                    );

                    // 7. Set the new token in token storage and session
                    $tokenStorage->setToken($token);
                    $request->getSession()->set('_security_main', serialize($token));
                    $request->getSession()->save(); // Ensure session changes are saved

                    // 8. Log and notify success
                    $this->logger->info('User role upgraded to admin', [
                        'user_id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'roles' => $user->getRoles()
                    ]);
                    
                    $this->addFlash('success', 'You are now an admin!');
                    return $this->redirectToRoute('app_admin');

                } catch (\Exception $e) {
                    $this->logger->error('Role upgrade failed', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->getId()
                    ]);
                    $this->addFlash('error', 'An error occurred during role upgrade.');
                }
            } else {
                $this->addFlash('info', 'You are already an admin.');
                return $this->redirectToRoute('app_admin');
            }
        } else {
            $this->logger->warning('Failed admin upgrade attempt', [
                'user_id' => $user->getId(),
                'ip' => $request->getClientIp()
            ]);
            $this->addFlash('error', 'Incorrect admin password.');
        }
    }

    // 9. Render the upgrade form
    return $this->render('admin_upgrade/index.html.twig');
}


    private function verifyAdminPassword(string $password): bool
    {
        // In production, you should:
        // 1. Store admin password hash in environment variables
        // 2. Use Symfony's password hasher component
        // This is simplified for development:
        return $password === 'admin123';
    }
}