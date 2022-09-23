<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function register(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $nomUser = "";
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData() == $form->get('plainPasswordBis')->getData()) {

                $user = $userRepository->findOneBy([
                    'username' => $nomUser
                ]);

                if ($user != null) {
                    $this->get('session')->getFlashBag()->add('notification', 'L\'utilisateur ' . $nomUser . ' possède déjà un compte');
                    return $this->redirectToRoute('app_register');
                } else {

                    $user = new User();
                    $nomUser = $form->get('pseudo')->getData();

                    $user->setRoles(array("ROLE_USER"));

                    $user->setUsername($nomUser);
                                        
                    $hashedPassword = $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    );
                    $user->setPassword($hashedPassword);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_login');
                }
            } else {
                $session->getFlashBag()->add('notification', 'Les Mot-de-passe ne sont pas similaires');
                return $this->redirectToRoute('app_registration');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'nomUser' => $nomUser
        ]);
    }
}
