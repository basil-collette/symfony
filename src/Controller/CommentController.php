<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\CommentRepository;
use App\Repository\ArticleRepository;

#[Route('/comment', name: 'app_comment')]
class CommentController extends AbstractController
{
    #[Route('/write', name: '_write')]
    #[IsGranted('ROLE_USER')]
    public function writeComment(CommentRepository $commentRepository, ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $articleRepository->findOneById($form->get('article')->getData());
            
            $comment->setContenu($form->get('contenu')->getData());
            $comment->setAuteur($this->getUser());
            $comment->setDate(new \DateTime());
            $comment->setArticle($article);

            $entityManager->persist($comment);
            $entityManager->flush();
        }

        //return new Response('<p>TEST</p>');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/like', name: '_like')]
    #[IsGranted('ROLE_USER')]
    public function like(CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $idComment = intval($request->request->get('commentId'));
        $comment = $commentRepository->find($idComment);

        $comment->addLikes($this->getUser()->getId());

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirect('/article_get/' . $idArticle);
    }

    #[Route('/unlike', name: '_unlike')]
    #[IsGranted('ROLE_USER')]
    public function unlike(CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $idComment = intval($request->request->get('commentId'));
        $comment = $commentRepository->find($idComment);

        $comment->removeLikes($this->getUser()->getId());

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirect('/article_get/' . $idArticle);
    }

    #[Route('/dislike', name: '_dislike')]
    #[IsGranted('ROLE_USER')]
    public function dislike(CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $idComment = intval($request->request->get('commentId'));
        $comment = $commentRepository->find($idComment);

        $comment->addDislikes($this->getUser()->getId());

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirect('/article_get/' . $idArticle);
    }

    #[Route('/undislike', name: '_undislike')]
    #[IsGranted('ROLE_USER')]
    public function undislike(CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idArticle = intval($request->request->get('articleId'));
        $idComment = intval($request->request->get('commentId'));
        $comment = $commentRepository->find($idComment);

        $comment->removeDislikes($this->getUser()->getId());

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirect('/article_get/' . $idArticle);
    }
}
