<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_profil')]
    public function index(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $session = $request->getSession();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData() == $form->get('passwordBis')->getData()) {

                $username = ($form->get('username')->getData()) ? $form->get('username')->getData() : $user->getUsername();
                $user->setUsername($username);

                $roles = ($form->get('roles')->getData()) ? $form->get('roles')->getData() : $user->getRoles();
                $user->setRoles($roles);
                   
                if ($form->get('password')->getData()) {
                    $hashedPassword = $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    );
                    $user->setPassword($hashedPassword);
                }

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');

            } else {
                $session->getFlashBag()->add('notification', 'Les Mot-de-passe ne sont pas similaires');
                return $this->redirectToRoute('app_profil');
            }
        } else {
            $form->get('password')->setData('');
        }
        
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'userForm' => $form->createView(),
            'user' => $user
        ]);
    }
}
