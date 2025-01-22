<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupFormType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Form\InviteUserFormType;
use App\Repository\UserRepository;

/**
 * Contrôleur pour gérer les groupes.
 */
class GroupController extends AbstractController
{
    /**
     * Affiche la liste de tous les groupes.
     *
     * @Route("/groups", name="groups")
     *
     * @param GroupRepository $groupRepository Le dépôt des groupes.
     * @return Response La réponse HTTP contenant la liste des groupes.
     */
    #[Route('/groups', name: 'groups')]
    public function index(GroupRepository $groupRepository): Response
    {
        $groups = $groupRepository->findAll();

        $user = $this->getUser();

        return $this->render('group/index.html.twig', [
            'groups' => $groups,
            'user' => $user,
        ]);
    }

    /**
     * Affiche les détails d'un groupe spécifique.
     *
     * @Route("/group/show/{id}", name="group_show", requirements={"id"="\d+"})
     *
     * @param int $id L'identifiant du groupe.
     * @param GroupRepository $groupRepository Le dépôt des groupes.
     * @param Request $request La requête HTTP.
     * @return Response La réponse HTTP affichant les détails du groupe.
     */

    #[Route('/group/show/{id}', name: 'group_show', requirements: ['id' => '\d+'])]
    public function show(int $id, GroupRepository $groupRepository, Request $request): Response
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        $user = $this->getUser();
        if($group->isPrivate() && !$user->getGroups()->contains($group)) {
            $this->addFlash('error', 'You do not have access to this group');
            return $this->redirectToRoute('groups');
        }

        if($user->getGroups()->contains($group)) {
            $serverIp = $this->getServerIp();
    
            // Détecter si l'adresse IP est valide, sinon fallback sur localhost
            $websocketServer = filter_var($serverIp, FILTER_VALIDATE_IP) ? "ws://{$serverIp}:8085" : 'ws://localhost:8085';    
        } else {
            $serverIp = null;
            $websocketServer = null;
        }



        return $this->render('group/show.html.twig', [
            'group' => $group,
            'user' => $this->getUser(),
            'group_id' => $id,
            'websocket_server' => $websocketServer,
            'auth_token' => 'your-secure-token',
        ]);
    }

    /**
     * Récupère l'adresse IP du serveur.
     *
     * @return string Retourne l'adresse IP locale détectée, ou '127.0.0.1' par défaut.
     */
    private function getServerIp(): string
    {
        // Initialisation par défaut
        $ip = '127.0.0.1';
    
        // Exécuter la commande 'ipconfig' sur Windows pour récupérer les adresses IP
        if (stripos(PHP_OS, 'WIN') === 0) {
            $output = [];
            exec('ipconfig', $output);
    
            foreach ($output as $line) {
                // Vérifier en anglais (IPv4 Address)
                if (preg_match('/IPv4 Address.*?:\s*([\d.]+)/', $line, $matches)) {
                    $ip = $matches[1];
                    break;
                }
                // Vérifier en français (Adresse IPv4)
                if (preg_match('/Adresse IPv4.*?:\s*([\d.]+)/', $line, $matches)) {
                    $ip = $matches[1];
                    break;
                }
            }
        } else {
            // Utiliser 'ifconfig' sur Linux/Mac pour récupérer les adresses IP
            $output = [];
            exec('ifconfig', $output);
    
            foreach ($output as $line) {
                if (preg_match('/inet\s+([\d.]+)\s+netmask/', $line, $matches) && $matches[1] !== '127.0.0.1') {
                    $ip = $matches[1];
                    break;
                }
            }
        }
    
        return $ip;
    }

    /**
     * Crée un nouveau groupe.
     *
     * @Route("/group/new", name="group_new")
     *
     * @param Request $request La requête HTTP.
     * @param ValidatorInterface $validator Le validateur des entités.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP affichant le formulaire de création.
     */
    #[Route('/group/new', name: 'group_new')]
    public function new(
        Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager,
    ): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to create an group.');
        }

        $group = new Group();
        $group->setOwner($user);
        $group->setSite($user->getSite());
        $group->addUser($user);

        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);

        $errors = $validator->validate($group);
        if (count($errors) > 0) {
            return $this->render('group/new.html.twig', [
                'form' => $form->createView(),
                'errors' => $errors,
            ]);
        }

        //Traiement de la bannière
        $banner = $form->get('banner')->getData();
        if ($banner) {
            $bannerName = md5(uniqid()) . '.' . $banner->guessExtension();
            $banner->move($this->getParameter('banners_directory'), $bannerName);
            $group->setBanner($bannerName);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($group);
            $entityManager->flush();
            $this->addFlash('success', 'Group created successfully');
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        return $this->render('group/new.html.twig', [
            'groupForm' => $form->createView(),
        ]);
    }

    /**
     * Modifie un groupe existant.
     *
     * @Route("/group/{id}/edit", name="group_edit", requirements={"id"="\d+"})
     *
     * @param Request $request La requête HTTP.
     * @param GroupRepository $groupRepository Le dépôt des groupes.
     * @param int $id L'identifiant du groupe.
     * @param EntityManagerInterface $em Le gestionnaire d'entités.
     * @param ValidatorInterface $validator Le validateur des entités.
     * @return Response La réponse HTTP affichant le formulaire d'édition.
     */
    #[Route('/group/{id}/edit', name: 'group_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Request $request, GroupRepository $groupRepository, int $id, EntityManagerInterface $em
    ): Response {
        $group = $groupRepository->find($id);
    
        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }
    
        $form = $this->createForm(GroupFormType::class, $group);
        $form->handleRequest($request);
    
        // Vérifiez si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            //Traiement de la bannière
            $banner = $form->get('banner')->getData();
            if ($banner) {
                $bannerName = md5(uniqid()) . '.' . $banner->guessExtension();
                $banner->move($this->getParameter('banners_directory'), $bannerName);
                $group->setBanner($bannerName);
            }
            $em->flush();
    
            $this->addFlash('success', 'Group updated successfully');
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }
    
        // Renvoyez le formulaire avec des erreurs (si existantes)
        return $this->render('group/edit.html.twig', [
            'groupForm' => $form->createView(),
            'group' => $group, // Optionnel pour afficher des informations sur le groupe
        ]);
    }
    

    /**
     * Supprime un groupe.
     *
     * @Route("/group/{id}/delete", name="group_delete", requirements={"id"="\d+"})
     *
     * @param int $id L'identifiant du groupe.
     * @param GroupRepository $groupRepository Le dépôt des groupes.
     * @param EntityManagerInterface $em Le gestionnaire d'entités.
     * @return Response La réponse HTTP redirigeant vers la liste des groupes.
     */
    #[Route('/group/{id}/delete', name: 'group_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, GroupRepository $groupRepository, EntityManagerInterface $em): Response
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        $em->remove($group);
        $em->flush();
        $this->addFlash('success', 'Group deleted successfully');

        return $this->redirectToRoute('groups');
    }

    #[Route('/group/{id}/join', name: 'group_join')]
    public function join(int $id, GroupRepository $groupRepository, EntityManagerInterface $em): Response
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        $user = $this->getUser();

        if($user->getGroups()->contains($group)) {
            $this->addFlash('error', 'You are already a member of this group');
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        if ($group->isPrivate()) {
            $this->addFlash('error', 'You cannot join a private group');
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        $user = $this->getUser();
        $group->addUser($user);

        $em->flush();
        $this->addFlash('success', 'You have joined the group successfully');

        return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
    }

    #[Route('/group/{id}/leave', name: 'group_leave')]
    public function leave(int $id, GroupRepository $groupRepository, EntityManagerInterface $em): Response
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        $user = $this->getUser();

        if(!$user->getGroups()->contains($group)) {
            $this->addFlash('error', 'You are not a member of this group');
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        if($group->getOwner() === $user) {
            $this->addFlash('error', 'You cannot leave a group you own');
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        $group->removeUser($user);

        $em->flush();
        $this->addFlash('success', 'You have left the group successfully');

        return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
    }

    #[Route('/group/{id}/invite', name: 'group_invite')]
    public function invite(
        int $id,
        Request $request,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $group = $groupRepository->find($id);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        // Vérifiez si l'utilisateur actuel est le propriétaire du groupe
        if ($group->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Only the owner can invite users.');
        }

        $form = $this->createForm(InviteUserFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $usernameOrEmail = $data['usernameOrEmail'];

            // Recherchez l'utilisateur par email ou username
            $user = $userRepository->findOneBy(['email' => $usernameOrEmail]) ??
                    $userRepository->findOneBy(['username' => $usernameOrEmail]);

            if (!$user) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
            }

            // Ajoutez l'utilisateur au groupe
            $group->addUser($user);
            $em->flush();

            $this->addFlash('success', 'User invited successfully.');
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        return $this->render('group/invite.html.twig', [
            'inviteForm' => $form->createView(),
            'group' => $group,
        ]);
    }
}

