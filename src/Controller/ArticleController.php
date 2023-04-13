<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


#[Route('/admin')]
class ArticleController extends AbstractController
{
    #[Route('/ajouter-un-article', name: 'create_article', methods: ['GET', 'POST'])]
    public function createArticle(Request $request, ArticleRepository $repository, SluggerInterface $slugger): Response
    {

        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());

            $article->setAlias($slugger->slug($article->getTitle()));

            # Set de la relation entre Article et User
            $article->setAuthor($this->getUser());

            $photo = $form->get('photo')->getData();

            if($photo) {
                $this->handleFile($article, $photo, $slugger);
            }

            $repository->save($article, true);

            $this->addFlash('success', "L'article est en ligne avec succès !");
            return $this->redirectToRoute('show_dashboard');
        }

        return $this->render('admin/article/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/modifier-un-article/{id}', name: 'update_article', methods: ['GET', 'POST'])]
    public function updateArticle(Article $article, Request $request, ArticleRepository $repository, SluggerInterface $slugger): Response
    {

        $currentPhoto = $article->getPhoto();

        $form = $this->createForm(ArticleFormType::class, $article, [
            'photo' => $currentPhoto
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $article->setUpdatedAt(new DateTime());

            $article->setAlias($slugger->slug($article->getTitle()));

            // # Set de la relation entre Article et User
            // $article->setAuthor($this->getUser());

            $newphoto = $form->get('photo')->getData();

            if($newphoto) {
                $this->handleFile($article, $newphoto, $slugger);
                #Si une nouvelle photo est uploadé, on va suprimer l'ancienne 
                unlink($this->getParameter('uploads_dir') . DIRECTORY_SEPARATOR . $currentPhoto);
            }
            else{
                $article->setPhoto($currentPhoto);
            }

            $repository->save($article, true);

            $this->addFlash('success', "L'article a bie été modifié avec succès !");
            return $this->redirectToRoute('show_dashboard');
        }

        return $this->render('admin/article/form.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }


    #[Route('/archiver-un-article/{id}', name: 'soft_delete_article', methods:['GET'])]
    public function softDeleteArticle(Article $article, ArticleRepository $repository):Response
    {
        $article->setDeletedAt(new DateTime());

        $repository->save($article, true);

        $this->addFlash('success', "L'article " . $article->getTitle() . "a bien été archivé");

        return $this->redirectToRoute('show_dashboard');
    }


    #[Route('/restaurer-un-article/{id}', name: 'restore_article', methods:['GET'])]
    public function restoreArticle(Article $article, ArticleRepository $repository):Response
    {
        $article->setDeletedAt(null);

        $repository->save($article, true);

        $this->addFlash('success', "L'article " . $article->getTitle() . "a bien été restauré");

        return $this->redirectToRoute('show_dashboard');
    }
    

    #[Route('/supprimer-un-article/{id}', name: 'hard_delete_article', methods:['GET'])]
    public function hardDeleteCategory(Article $article, ArticleRepository $repository):Response
    {
        $photo = $article->getPhoto();
        $repository->remove($article, true);

        unlink($this->getParameter('uploads_dir') . DIRECTORY_SEPARATOR . $photo);

        $this->addFlash('success', "L'article " . $article->getTitle() . "a bien été supprimé definitivement");

        return $this->redirectToRoute('show_dashboard');
    }





    private function handleFile(Article $article, UploadedFile $photo, SluggerInterface $slugger)
    {
        $extension = '.' . $photo->guessExtension();

        $safeFilename = $slugger->slug(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME));

        $newFilename = $safeFilename . '_' . uniqid() . $extension;

        try {
            $photo->move($this->getParameter('uploads_dir'), $newFilename);

            $article->setPhoto($newFilename);
        } catch (FileException $exception) {
            $this->addFlash('warning', "Le fichier photo ne s'est pas importé correctement. Veuillez réessayer." . $exception->getMessage());
        }
    }
}
