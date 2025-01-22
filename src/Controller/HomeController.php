<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\FileUploader;
use App\Repository\SeasonIdeaRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Contrôleur pour les fonctionnalités principales de l'application.
 */
class HomeController extends AbstractController
{
    /**
     * @var string Chemin vers le répertoire de stockage des fichiers uploadés.
     */
    private string $uploadsDir;
    private TranslatorInterface $translator;

    /**
     * Constructeur du contrôleur.
     *
     * @param string $uploadsDir Chemin vers le répertoire des uploads.
     */
    public function __construct(string $uploadsDir, TranslatorInterface $translator)
    {
        $this->uploadsDir = $uploadsDir;
        $this->translator = $translator;
    }

    /**
     * Affiche la page d'accueil avec la branche GitHub actuelle.
     *
     * @Route(['/', '/index', '/home'], name="home")
     *
     * @return Response Retourne une réponse HTTP avec la vue de la page d'accueil.
     */
    #[Route(['/', '/index', '/home'], name: 'home')]
    public function index(SeasonIdeaRepository $seasonIdeaRepository): Response
    {
        // Détection de la saison actuelle
        $month = (int) (new \DateTime())->format('m');
        $seasons = [
            'winter' => ['months' => [12, 1, 2], 'name' => 'winter', 'background' => 'assets/images/winter-bg.jpg'],
            'spring' => ['months' => [3, 4, 5], 'name' => 'spring', 'background' => 'assets/images/spring-bg.jpg'],
            'summer' => ['months' => [6, 7, 8], 'name' => 'summer', 'background' => 'assets/images/summer-bg.jpg'],
            'autumn' => ['months' => [9, 10, 11], 'name' => 'automn', 'background' => 'assets/images/autumn-bg.jpg'],
        ];
    
        $currentSeason = array_filter($seasons, fn($s) => in_array($month, $s['months']))[0] ?? $seasons['winter'];
    
        $seasonIdeas = $seasonIdeaRepository->findBy(['season' => strtolower($currentSeason['name'])]);
    
        // Récupérer la branche GitHub actuelle
        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD')) ?: 'Unknown branch';
    
        return $this->render('home/index.html.twig', [
            'branch' => $branch,
            'currentSeason' => $currentSeason['name'],
            'seasonBackground' => $currentSeason['background'],
            'seasonIdeas' => $seasonIdeas,
            'seasonDescription' => "event_ideas_season_description",
            'translations' => [
                'event_ideas_title' => 'event_ideas_title',
                'event_ideas_no_ideas' => 'event_ideas_no_ideas',
            ],
        ]);
        
    }

    /**
     * Affiche et traite le formulaire de profil utilisateur.
     *
     * @Route("/profile", name="app_profile")
     *
     * @param Request $request Requête HTTP entrante.
     * @param ManagerRegistry $doctrine Gestionnaire d'entité pour la persistance.
     * @param SiteRepository $siteRepository Dépôt pour accéder aux entités Site.
     *
     * @return Response Retourne une réponse HTTP avec la vue de profil utilisateur.
     */
    #[Route('/profile', name: 'app_profile')]
    public function profile(
        Request $request,
        ManagerRegistry $doctrine,
        SiteRepository $siteRepository,
        FileUploader $fileUploader
    ): Response {
        $user = $this->getUser();
        $sites = $siteRepository->findAll(); // Récupérer tous les sites
    
        $form = $this->createForm(UserType::class, $user, [
            'sites' => $sites, // Passer les sites comme option
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('profilePicture')->getData();
            if ($uploadedFile) {
                // Supprimer l'ancienne image si elle existe
                if ($user->getProfilePicture()) {
                    $existingFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $user->getProfilePicture();
                    $fileUploader->delete($existingFilePath);
                }
    
                // Télécharger la nouvelle image
                $newFilename = $user->getId() . '.' . $uploadedFile->guessExtension();
                $filePath = $fileUploader->upload($uploadedFile, $newFilename);
    
                // Mettre à jour le chemin dans l'entité utilisateur
                $user->setProfilePicture('images/avatars/' . basename($filePath));
            }
    
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
    
            $this->addFlash('success', $this->translator->trans('success.profile_updated'));
            return $this->redirectToRoute('app_profile');
        }
    
        return $this->render('profile/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Affiche le profil d'un utilisateur spécifique.
     *
     * @Route("/profile/{id}", name="app_profile_view")
     *
     * @param int $id Identifiant de l'utilisateur.
     * @param ManagerRegistry $doctrine Gestionnaire d'entité pour la persistance.
     *
     * @return Response Retourne une réponse HTTP avec la vue du profil utilisateur.
     * @throws NotFoundHttpException Si l'utilisateur n'est pas trouvé.
     */
    #[Route('/profile/{id}', name: 'app_profile_view',requirements: ['id' => '\d+'])]
    public function viewProfile(int $id, ManagerRegistry $doctrine): Response
    {
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('profile/view.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Affiche la page "À propos" avec les administrateurs de l'application.
     *
     * @Route("/about", name="app_about")
     *
     * @param UserRepository $userRepository Dépôt pour accéder aux entités User.
     *
     * @return Response Retourne une réponse HTTP avec la vue "À propos".
     */
    #[Route('/about', name: 'app_about')]
    public function about(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs ayant le rôle 'ROLE_ADMIN'
        $admins = $userRepository->findByIsAdmin('1');
    
        return $this->render('home/about.html.twig', [
            'admins' => $admins,
        ]);
    }

    /**
     * Permet à l'utilisateur connecté de suivre un autre utilisateur.
     *
     * @Route("/user/{id}/follow", name="user_follow", methods={"POST"})
     *
     * @param User $user L'utilisateur à suivre.
     * @param EntityManagerInterface $em Le gestionnaire d'entité.
     * @return Response Une redirection vers le profil de l'utilisateur suivi.
     */
    #[Route('/user/{id}/follow', name: 'user_follow', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function follow(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();
        $currentUser->follow($user);
        $em->flush();

        return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
    }

    /**
     * Permet à l'utilisateur connecté de ne plus suivre un autre utilisateur.
     *
     * @Route("/user/{id}/unfollow", name="user_unfollow", methods={"POST"})
     *
     * @param User $user L'utilisateur à ne plus suivre.
     * @param EntityManagerInterface $em Le gestionnaire d'entité.
     * @return Response Une redirection vers le profil de l'utilisateur désabonné.
     */
    #[Route('/user/{id}/unfollow', name: 'user_unfollow', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function unfollow(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();
        $currentUser->unfollow($user);
        $em->flush();

        return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
    }

    /**
     * Récupère les détails du profil d'un utilisateur spécifique via une API.
     *
     * @Route("/api/profile-details/{id}", name="api_profile_details", methods={"GET"})
     *
     * @param int $id L'identifiant de l'utilisateur.
     * @param UserRepository $userRepository Le dépôt des utilisateurs.
     * @return JsonResponse La réponse JSON contenant les détails de l'utilisateur ou un message d'erreur si non trouvé.
     */
    #[Route('/api/profile-details/{id}', name: 'api_profile_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getEventDetails(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'lastname' => $user->getLastname(),
            'firstname' => $user->getFirstname(),
            'link' => $this->generateUrl('app_profile', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
