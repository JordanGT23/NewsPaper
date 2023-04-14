<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Commentary;
use App\Form\ChangePasswordFormType;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'app_register', methods:['GET', 'POST'])]
    public function register(Request $request, UserRepository $repository, UserPasswordHasherInterface $passwordHasher): Response
    {

        // if($this->getUser()){
        //     $this->addFlash('warning', "Vous êtes connecté, inscription non autorisée. <a href='/logout'>Déconnexion</a>");
        //     return $this->redirectToRoute('show_home');
        // }

        $user = new User();

        $form = $this->createForm(RegisterFormType::class, $user)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            $repository->save($user, true);

            $this->addFlash('success', "Votre inscription a été correctement enregistrée !!!");

            return $this->redirectToRoute("show_home");
        }

        return $this->render('user/register_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/profile/voir-mon-compte', name: 'show_account', methods:['GET'])]
    public function showProfile(EntityManagerInterface $entityManager):Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy(['author' => $this->getUser()]);
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['author' => $this->getUser()]);
        $user = $this->getUser();
        // dd($user);



        return $this->render('user/account.html.twig', [
            'user' => $user,
            'articles' => $articles,
            'commentaries' => $commentaries
        ]);
    }

    #[Route('/changer-mon-mot-de-passe', name: 'changer_password', methods:['GET', 'POST'])]
    public function changePassword(Request $request, UserRepository $repository, UserPasswordHasherInterface $hasher):Response
    {
        $form  = $this->createForm(ChangePasswordFormType::class)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            #Recuperer le User en BDD
            $user = $repository->find($this->getUser());

            /* Listing des etapes:
                1- Un nouvel input => ChangePasswordFormType
                2- Récuperer la valeur de cet input
                3- Hasher le $currentPassword pour la comparaison en BDD
                4- Condition de vérification
                5- Si la condition est verifié, alors en execute le code
            */

            //------------------------------VERIFICATION dU MDP-------------------
            $currentPassword = $form->get('currentPassword')->getData();

            if(! $hasher->isPasswordValid($user, $currentPassword)){
                $this->addFlash('warning', "le mot de passe actuel n'est pas valide.");
                return $this->redirectToRoute('show_account');
            }

            $user->setUpdatedAt(new DateTime());

            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($hasher->hashPassword($user, $plainPassword));

            $repository->save($user, true);

            $this->addFlash('success', "Le mot de passe a bien été modifié.");
            return $this->redirectToRoute('show_account');
        }

        return $this->render('user/change_password_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
