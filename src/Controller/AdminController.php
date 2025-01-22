<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CsvUploadType;
use App\Repository\EventRepository;
use App\Service\CsvUserImporter;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Contrôleur d'administration pour gérer les actions réservées aux administrateurs,
 * telles que la gestion des utilisateurs et des événements, ainsi que le lancement/arrêt
 * des workers.
 */
class AdminController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Affiche le tableau de bord de l'administrateur avec des informations sur l'application
     * et le serveur.
     *
     * @Route("/admin", name="admin_dashboard")
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour les interactions avec la base de données.
     * @return Response La réponse contenant la vue du tableau de bord.
     */
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        // Récupérer les informations du serveur et de l'application
        $appInfo = [
            'serverName' => php_uname('n'),
            'phpVersion' => phpversion(),
            'dbVersion' => $this->getDatabaseVersion($entityManager),
            'symfonyVersion' => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'environment' => $this->getParameter('kernel.environment'),
            'lastCommit' => trim(shell_exec('git log -1 --pretty=%h')),
            'dbName' => $entityManager->getConnection()->getDatabase(),
            'appVersion' => "1.0.4",
        ];

        // Rendre la vue avec les données nécessaires
        return $this->render('admin/dashboard.html.twig', [
            'appInfo' => $appInfo,
        ]);
    }

    /**
     * Démarre un worker en créant et exécutant un fichier batch pour consommer les messages
     * asynchrones.
     *
     * @Route("/admin/worker/start", name="start_worker", methods={"POST"})
     * @return JsonResponse Une réponse JSON indiquant le statut du démarrage du worker.
     */
    #[Route('/admin/worker/start', name: 'start_worker', methods: ['POST'])]
    public function startWorker(): JsonResponse
    {
        $projectRoot = dirname(__DIR__, 2); // Trouver la racine du projet
        $batchFile = $projectRoot . '\\run_worker.bat';

        // Créer un fichier batch pour exécuter la commande
        $batchContent = sprintf(
            "@echo off\nphp %s\\bin\\console messenger:consume async --time-limit=3600 --memory-limit=128M -vv",
            $projectRoot
        );

        try {
            // Écrire le contenu dans le fichier batch
            file_put_contents($batchFile, $batchContent);

            // Lancer le fichier batch avec 'start'
            $command = sprintf('start /B cmd /C "%s"', $batchFile);
            shell_exec($command);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Worker started successfully.',
                'workingDirectory' => $projectRoot,
                'batchFile' => $batchFile,
                'command' => $command,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to start the worker: ' . $e->getMessage(),
                'workingDirectory' => $projectRoot,
            ], 500);
        }
    }

    /**
     * Arrête le worker en terminant le processus lié à la consommation de messages asynchrones.
     *
     * @Route("/admin/worker/stop", name="stop_worker", methods={"POST"})
     * @return Response La réponse redirigeant vers la vue d'application de l'administration.
     */
    #[Route('/admin/worker/stop', name: 'stop_worker', methods: ['POST'])]
    public function stopWorker(): Response
    {
        // Commande pour arrêter le worker
        shell_exec('pkill -f "messenger:consume async"');
        $this->addFlash('success', 'Worker stopped successfully.');

        return $this->redirectToRoute('admin_application');
    }

    /**
     * Affiche la page d'administration des applications, y compris les informations
     * sur les utilisateurs et les événements.
     *
     * @Route("/admin/application", name="admin_application")
     * @param EntityManagerInterface $em Le gestionnaire d'entités pour les interactions avec la base de données.
     * @param EventRepository $eventRepo Le dépôt des événements pour interagir avec les entités d'événements.
     * @param UserRepository $userRepo Le dépôt des utilisateurs pour interagir avec les entités utilisateurs.
     * @return Response La réponse contenant la vue de l'application d'administration.
     */
    #[Route('/admin/application', name: 'admin_application')]
    public function application(EntityManagerInterface $em, EventRepository $eventRepo, UserRepository $userRepo): Response
    {
        $connection = $em->getConnection();
        $stmt = $connection->prepare('SELECT COUNT(*) as count FROM messenger_messages');
        $result = $stmt->executeQuery()->fetchAssociative();

        // Vérifier si le worker est en cours d'exécution
        $workerRunning = false;
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('powershell.exe Get-Process -Name "php"');
            $workerRunning = stripos($output, 'messenger:consume async') !== false;
        } else {
            $workerRunning = !empty(shell_exec('pgrep -f "messenger:consume async"'));
        }

        // Graphique utilisateurs
        $usersData = [
            'labels' => ['January', 'February', 'March', 'April'], // Exemple de labels
            'values' => [50, 75, 100, 125], // Exemple de données
        ];

        // Graphique événements
        $events = $eventRepo->findAll();
        $eventLabels = array_map(fn($event) => $event->getName(), $events);
        $eventData = array_map(fn($event) => count($event->getParticipants()), $events);

        // Récupérez les statistiques du système
        $stats = $this->getSystemStats();

        return $this->render('admin/application.html.twig', [
            'remainingTasks' => $result['count'],
            'workerRunning' => $workerRunning,
            'systemStats' => $stats,
            'usersData' => $usersData,
            'eventsData' => [
                'labels' => $eventLabels,
                'values' => $eventData,
            ],
        ]);
    }

    /**
     * Vérifie l'état du worker et retourne une réponse JSON indiquant s'il est en cours d'exécution.
     *
     * @Route("/admin/worker/status", name="worker_status", methods={"GET"})
     * @return JsonResponse Une réponse JSON contenant le statut du worker.
     */
    #[Route('/admin/worker/status', name: 'worker_status', methods: ['GET'])]
    public function workerStatus(): JsonResponse
    {
        $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe"');
        $isRunning = strpos($output, 'php.exe') !== false;

        return new JsonResponse([
            'status' => 'success',
            'workerRunning' => $isRunning,
            'output' => $output,
        ]);
    }

    /**
     * Vérifie l'état du worker et retourne une réponse JSON indiquant s'il est en cours d'exécution.
     *
     * @Route("/admin/worker/status", name="worker_status", methods={"GET"})
     * @return JsonResponse Une réponse JSON contenant le statut du worker.
     */
    private function getSystemStats(): array
    {
        return [
            'memoryUsage' => round(memory_get_usage(true) / 1024 / 1024, 2), // MB
            'cpuLoad' => function_exists('sys_getloadavg')
                ? sys_getloadavg()[0]
                : (PHP_OS_FAMILY === 'Windows'
                    ? trim(shell_exec("wmic cpu get loadpercentage")) . '%'
                    : 'Unavailable'),
            'diskUsage' => round((disk_total_space('/') - disk_free_space('/')) / disk_total_space('/') * 100, 2), // Percentage
        ];
    }

    /**
     * Récupère la version de la base de données en fonction de la plateforme utilisée.
     *
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour interagir avec la base de données.
     * @return string La version de la base de données ou un message indiquant une version inconnue.
     */
    private function getDatabaseVersion(EntityManagerInterface $entityManager): string
    {
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        try {
            return $connection->fetchOne('SELECT VERSION()');
        } catch (\Exception $e) {
            return $platform->getName() . ' (unknown version)';
        }
    }

    /**
     * Affiche la liste des utilisateurs pour les administrateurs.
     *
     * @Route("/admin/users", name="admin_users")
     * @param UserRepository $repo Le dépôt des utilisateurs pour interagir avec les entités utilisateurs.
     * @return Response La réponse contenant la vue avec la liste des utilisateurs.
     */
    #[Route('/admin/users', name: 'admin_users')]
    public function users(
        UserRepository $repo,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
            }
        else {
            $qb = $repo->createQueryBuilder('u')
                ->orderBy('u.username', 'DESC');

            // Paginer les utilisateurs.
            $pagination = $paginator->paginate(
                $qb,
                $request->query->getInt('page', 1),
                12
            );

            return $this->render('admin/users.html.twig', [
                'users' => $pagination,
            ]);
        }
    }

    /**
     * Supprime un utilisateur donné s'il est validé via un token CSRF et redirige vers la liste des utilisateurs.
     *
     * @Route("/admin/users/delete/{id}", name="admin_user_delete")
     * @param User $user L'utilisateur à supprimer.
     * @param Request $request La requête HTTP pour valider le token CSRF.
     * @param EntityManagerInterface $em Le gestionnaire d'entités pour supprimer l'utilisateur de la base de données.
     * @return Response La réponse redirigeant vers la vue des utilisateurs après suppression.
     */
    #[Route('/admin/users/delete/{id}', name: 'admin_user_delete', requirements: ['id' => '\d+'])]
    public function deleteUser( User $user, Request $request, EntityManagerInterface $em): Response
    {
        $actualUser = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$actualUser || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($user);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('success.user_deleted'));
                // Penser ici à gérer toutes la partie cascade parce que delete risque de poser problème pour les événements en cours ?
            }
            catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('error.user_delete_failed'));
            }
        }
        return $this->redirectToRoute('admin_users');
    }

    /**
     * Désactive un utilisateur donné et redirige vers la page de profil.
     *
     * @Route("/admin/users/desactivate/{id}", name="admin_user_desactivate")
     * @param EntityManagerInterface $em Le gestionnaire d'entités pour sauvegarder les modifications.
     * @param User $user L'utilisateur à désactiver.
     * @return Response La réponse redirigeant vers la page de profil de l'utilisateur.
     */
    #[Route('/admin/users/desactivate/{id}', name: 'admin_user_desactivate', requirements: ['id' => '\d+'])]
    public function desactivateUser(EntityManagerInterface $em, User $user): Response
    {
        $actualUser = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$actualUser || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        $user->setIsActive(false);
        $em->persist($user);
        $em->flush();
        $this->addFlash('success', $this->translator->trans('success.user_deactivated'));
        return $this->redirectToRoute('app_profile_view', ['id' => $user->getId()]);
    }

    /**
     * Active un utilisateur donné et redirige vers la page de profil.
     *
     * @Route("/admin/users/activate/{id}", name="admin_user_activate")
     * @param EntityManagerInterface $em Le gestionnaire d'entités pour sauvegarder les modifications.
     * @param User $user L'utilisateur à activer.
     * @return Response La réponse redirigeant vers la page de profil de l'utilisateur.
     */
    #[Route('/admin/users/activate/{id}', name: 'admin_user_activate',requirements: ['id' => '\d+'])]
    public function activateUser(EntityManagerInterface $em, User $user): Response
    {
        $actualUser = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$actualUser || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        $user->setIsActive(true);
        $em->persist($user);
        $em->flush();
        $this->addFlash('success', $this->translator->trans('success.user_activated'));
        return $this->redirectToRoute('app_profile_view', ['id' => $user->getId()]);
    }

    /**
     * Affiche le formulaire pour importer des utilisateurs depuis un fichier CSV.
     *
     * @Route("/admin/import-users", name="admin_import_users")
     * @param Request $request La requête HTTP pour traiter le formulaire d'importation CSV.
     * @param CsvUserImporter $csvUserImporter Service pour gérer l'importation d'utilisateurs depuis un fichier CSV.
     * @param SessionInterface $session La session utilisateur pour stocker les données d'aperçu.
     * @return Response La réponse contenant le formulaire d'importation d'utilisateurs.
     */
    #[Route('/admin/import-users', name: 'admin_import_users')]
    public function importUsers(Request $request, CsvUserImporter $csvUserImporter, SessionInterface $session): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(CsvUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('file')->getData();
            $filePath = $uploadedFile->getPathname();

            try {
                // Préparer les données pour prévisualisation avec validation des doublons
                $previewData = $csvUserImporter->previewWithValidation($filePath);

                // Stocker dans la session pour confirmation ultérieure
                $session->set('csv_preview', $previewData);

                return $this->render('admin/preview_users.html.twig', [
                    'previewData' => $previewData,
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/import_users.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Confirme l'importation des utilisateurs après un aperçu et envoie des emails si spécifié.
     *
     * @Route("/admin/confirm-import", name="admin_confirm_import")
     * @param Request $request La requête HTTP pour traiter la confirmation de l'importation.
     * @param CsvUserImporter $csvUserImporter Service pour finaliser l'importation des utilisateurs.
     * @param MailerInterface $mailer Service pour envoyer des emails.
     * @param UrlGeneratorInterface $urlGenerator Générateur de lien pour le lien de réinitialisation de mot de passe.
     * @param SessionInterface $session La session utilisateur pour récupérer les données d'aperçu.
     * @return Response La réponse redirigeant vers la page d'importation d'utilisateurs après confirmation.
     */
    #[Route('/admin/confirm-import', name: 'admin_confirm_import')]
    public function confirmImport(
        Request $request,
        CsvUserImporter $csvUserImporter,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        SessionInterface $session
    ): Response {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        $previewData = $session->get('csv_preview', []);

        if (empty($previewData)) {
            $this->addFlash('error', $this->translator->trans('error.no_data_to_import'));
            return $this->redirectToRoute('admin_import_users');
        }

        // Vérifier si l'option "Send Emails" est cochée
        $sendEmails = $request->request->get('send_emails') === 'on';

        try {
            $results = $csvUserImporter->finalizeImport($previewData);

            if ($sendEmails) {
                foreach ($previewData as $row) {
                    if (!empty($row['issues'])) {
                        continue; // Ignorer les lignes avec des problèmes
                    }

                    $resetUrl = $urlGenerator->generate('reset_password', [
                        'verificationToken' => $row['verificationToken'], // Le token est toujours disponible
                    ], UrlGeneratorInterface::ABSOLUTE_URL);

                    $email = (new Email())
                        ->from('elisa67228@gmail.com')
                        ->to($row['email'])
                        ->subject('Welcome! Set Your Password')
                        ->html($this->renderView('emails/invitation_email.html.twig', [
                            'user' => $row,
                            'resetUrl' => $resetUrl,
                        ]));

                    $mailer->send($email);
                }
                $this->addFlash('success', $this->translator->trans('success.invitation_emails_sent'));
            }

            $this->addFlash('success', $this->translator->trans('success.users_imported', ['%count%' => $results['success']]));
            if (!empty($results['errors'])) {
                $this->addFlash('error', implode('<br>', $results['errors']));
            }

            // Clear session data after import
            $session->remove('csv_preview');

            return $this->redirectToRoute('admin_import_users');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_import_users');
        }
    }

    /**
     * Affiche la liste des événements pour l'administrateur.
     *
     * @Route("/admin/events", name="admin_events")
     * @param EventRepository $repo Le dépôt des événements pour interagir avec les entités d'événements.
     * @return Response La réponse contenant la vue avec la liste des événements.
     */
    #[Route('/admin/events', name: 'admin_events')]
    public function events(EventRepository $repo): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        $user = $this->getUser();

        // Vérifier si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));

            return $this->redirectToRoute('home');
        }
        else {
            $events = $repo->findAll();

            return $this->render('admin/events.html.twig', [
                'events' => $events,
            ]);
        }
    }
    
}