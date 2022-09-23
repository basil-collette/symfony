<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Entity\Comment;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/article', name: 'app_article')]
class ArticleController extends AbstractController
{
    #[Route('_getall', name: '_getall')]
    public function getAll(ArticleRepository $articleRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $articles = $articleRepository->findAll();

        $paginatedArticles = $paginator->paginate(
            $articles, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            10 // Nombre de résultats par page
        );

        return $this->render('article/getall.html.twig', [
            'controller_name' => 'ArticleController',
            'paginatedArticles' => $paginatedArticles
        ]);
    }

    #[Route('_get/{id}', name: '_get')]
    public function get(ArticleRepository $articleRepository, $id): Response
    {
        $user = $this->getUser();

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $article = $articleRepository->findOneById($id);
        $form->get('article')->setData($article);

        return $this->render('article/get.html.twig', [
            'controller_name' => 'ArticleController',
            'user' => $user,
            'article' => $article,
            'commentForm' => $form->createView(),
            'idArticle' => $id
        ]);
    }

    #[Route('/write', name: '_write')]
    #[IsGranted('ROLE_USER')]
    public function writeArticle(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setTitre($form->get('titre')->getData());
            $article->setContenu($form->get('contenu')->getData());
            $article->setAuteur($this->getUser());
            $article->setDatetime(new \DateTime());

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_getall');
        }

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'article' => $article,
            'articleForm' => $form->createView(),
        ]);
    }

    #[Route('/like', name: '_like')]
    #[IsGranted('ROLE_USER')]
    public function like(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $article = $articleRepository->find($idArticle);
        $article->addLikes($this->getUser()->getId());

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->get($articleRepository, $idArticle);
    }

    #[Route('/unlike', name: '_unlike')]
    #[IsGranted('ROLE_USER')]
    public function unlike(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $article = $articleRepository->find($idArticle);
        $article->removeLikes($this->getUser()->getId());

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->get($articleRepository, $idArticle);
    }

    #[Route('/dislike', name: '_dislike')]
    #[IsGranted('ROLE_USER')]
    public function dislike(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $article = $articleRepository->find($idArticle);
        $article->addDislikes($this->getUser()->getId());

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->get($articleRepository, $idArticle);
    }

    #[Route('/undislike', name: '_undislike')]
    #[IsGranted('ROLE_USER')]
    public function undislike(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $article = $articleRepository->find($idArticle);
        $article->removeDislikes($this->getUser()->getId());

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->get($articleRepository, $idArticle);
    }
}
