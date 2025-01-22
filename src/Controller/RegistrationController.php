<?php

namespace App\Controller;

use App\Entity\User;
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
use App\Form\RegistrationFormType;

/**
 * Contrôleur pour la gestion de l'inscription des utilisateurs administrateurs.
 */
class RegistrationController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Permet à un administrateur de créer un nouvel utilisateur.
     *
     * @Route("/admin/register", name="app_register")
     *
     * @param Request $request Requête HTTP entrante.
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage de mot de passe.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entité pour la persistance.
     * @param MailerInterface $mailer Service d'envoi d'e-mails.
     * @param UrlGeneratorInterface $urlGenerator Générateur d'URL pour le lien de réinitialisation.
     *
     * @return Response Retourne une réponse HTTP avec la vue d'inscription.
     */
    #[Route('/admin/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $user = $this->getUser();

        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', $this->translator->trans('error.access_denied_admin'));
            return $this->redirectToRoute('home');
        }

        // Création du formulaire d'inscription
        $newUser = new User();
        
        $form = $this->createForm(RegistrationFormType::class, $newUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $verificationToken = bin2hex(random_bytes(32));
            $newUser->setVerificationToken($verificationToken);
            $newUser->setIsVerified(true);
            $newUser->setIsActive(true);

            $entityManager->persist($newUser);
            $entityManager->flush();

            $resetUrl = $urlGenerator->generate('reset_password', [
                'verificationToken' => $verificationToken,
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('elisa67228@gmail.com')
                ->to($newUser->getEmail())
                ->subject('Welcome! Set Your Password')
                ->html($this->renderView('emails/invitation_email.html.twig', [
                    'user' => $newUser,
                    'resetUrl' => $resetUrl,
                ]));

            $mailer->send($email);

            $this->addFlash('success', $this->translator->trans('success.user_registered'));
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
