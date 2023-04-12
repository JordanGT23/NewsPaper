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






    private function handleFile(Article $article, UploadedFile $photo, SluggerInterface $slugger)
    {
        $extension = '.' . $photo->guessExtension();

        $safeFilename = $slugger->slug(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME));

        $newFilename = $safeFilename . '_' . uniqid("", true) . $extension;

        try {
            $photo->move($this->getParameter('uploads_dir'), $newFilename);

            $article->setPhoto($newFilename);
        } catch (FileException $exception) {
            $this->addFlash('warning', "Le fichier photo ne s'est pas importé correctement. Veuillez réessayer." . $exception->getMessage());
        }
    }
}
