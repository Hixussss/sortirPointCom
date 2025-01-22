<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Contrôleur pour la gestion de la réinitialisation de mot de passe.
 */
class ForgotPasswordController extends AbstractController
{
    private TranslatorInterface $translator;

    /**
     * @var EntityManagerInterface Gestionnaire d'entité pour la persistance des données.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Constructeur du contrôleur.
     *
     * @param EntityManagerInterface $entityManager Gestionnaire d'entité pour accéder aux données de l'application.
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

     /**
     * Affiche le formulaire de demande de réinitialisation de mot de passe et gère la soumission.
     *
     * @Route("/forgot-password", name="app_forgot_password")
     *
     * @param Request $request Requête HTTP entrante.
     * @param UserRepository $userRepository Dépôt d'utilisateurs pour les opérations de recherche.
     * @param MailerInterface $mailer Service d'envoi de mails.
     *
     * @return Response Retourne une réponse HTTP avec le formulaire ou redirige après envoi de l'email.
     */
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', $this->translator->trans('error.email_not_found'));
                return $this->redirectToRoute('app_forgot_password');
            }

            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);


            $this->entityManager->flush();

            // Envoi de l'email
            $resetUrl = $this->generateUrl(
                'reset_password', 
                ['verificationToken' => $verificationToken], 
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $email = (new Email())
            ->from('elisa67228@gmail.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de mot de passe')
            ->html($this->renderView('emails/reset_password.html.twig', [
                'user' => $user,
                'resetUrl' => $resetUrl,
            ]));
        

            $mailer->send($email);

            $this->addFlash('success', $this->translator->trans('success.reset_email_sent'));
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe et gère la soumission.
     *
     * @Route("/reset-password/{verificationToken}", name="reset_password")
     *
     * @param Request $request Requête HTTP entrante.
     * @param string $verificationToken Jeton de vérification de la réinitialisation.
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage de mot de passe.
     * @param UserRepository $userRepository Dépôt d'utilisateurs pour les opérations de recherche.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entité pour persister les données.
     *
     * @return Response Retourne une réponse HTTP avec le formulaire ou redirige après réinitialisation.
     */
    #[Route('/reset-password/{verificationToken}', name: 'reset_password')]
    public function resetPassword(
        Request $request,
        string $verificationToken,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->findOneBy(['verificationToken' => $verificationToken]);
    
        if (!$user) {
            $this->addFlash('error', $this->translator->trans('error.invalid_reset_link'));
            return $this->redirectToRoute('app_login');
        }
    
        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password');
    
            if (empty($newPassword)) {
                $this->addFlash('error', $this->translator->trans('error.empty_password'));
            } else {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
    
                $user->setPassword($hashedPassword);
                $user->setVerificationToken(null);
    
                $entityManager->flush();
    
                $this->addFlash('success', $this->translator->trans('success.password_reset'));
                return $this->redirectToRoute('app_login');
            }
        }
    
        return $this->render('security/reset_password.html.twig', ['token' => $verificationToken]);
    }
}
