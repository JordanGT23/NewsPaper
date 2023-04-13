<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/tableau-de-bord', name: 'show_dashboard', methods:['GET'])]
    public function showDashboard(EntityManagerInterface $entityManager): Response
    {
        # Ce block de code try/catch() permet de bloquer l'acces et de rediriger si le role n'est pas bon.
        # Désactiver access_control dans config/packages/security.yaml !!(sinon cela ne fonctionne pas.)
        try{
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
        }
        catch(AccessDeniedException){
            $this->addFlash('danger', "Cette partie du site est réservé");
            return $this->redirectToRoute('app_login');
        }

        $categories = $entityManager->getRepository(Category::class)->findBy(['deletedAt' => null]);
        $articles = $entityManager->getRepository(Article::class)->findBy(['deletedAt' => null]);
        $users = $entityManager->getRepository(User::class)->findBy(['deletedAt' => null]);
        return $this->render('admin/show_dashboard.html.twig', [
            'categories' => $categories,
            'articles' => $articles,
            'users' => $users
        ]);
    }


    #[Route('voir-les-archives', name:'show_archives', methods: ['GET'])]
    public function showArchives(EntityManagerInterface $entityManager):Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAllArchived();
        $articles = $entityManager->getRepository(Article::class)->findAllArchived();

        return $this->render('admin/show_archive.html.twig', [
            'categories'  => $categories,
            'articles' => $articles
        ]);
    }

    #[Route('/modifier-role-user/{id}', name: 'modify_user_role', methods: ['GET'])]
    public function modifyUserRole(User $user, UserRepository $repository): Response
    {
        # - 1 : recuperer son role 2 : modifier le role 3 : save en BDD

        if(in_array('ROLE_USER', $user->getRoles())) {
            $user->setRoles(['ROLE_ADMIN']);
        }
        else {
            $user->setRoles(['ROLE_USER']);
        }

        $repository->save($user, true);

        return $this->redirectToRoute('show_dashboard');

    }
}
