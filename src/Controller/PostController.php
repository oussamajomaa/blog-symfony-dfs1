<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_post')]
    public function index(PostRepository $postRepository,CategoryRepository $categoryRepositoryn, SessionInterface $session): Response
    {
        $categories = $categoryRepositoryn->findAll();
        $session->set('categories',$categories);
        $posts = $postRepository->findBy([], ['createdAt' => 'desc']);

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/post/new', name: 'app_post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
 
        $post = new Post();
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $post->getImage();
            if (!$image) {
                $post->setImage('https://placehold.co/600x400/orange/white');
            }
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('app_post');
        }
        return $this->render('post/new.html.twig', [
            'form' => $form
        ]);
    }

   


    #[Route('/post/liked/{id}', name:'app_post_liked')]
    public function liked(Post $post, EntityManagerInterface $em):Response
    {
        if ($post->getLiked()) {
            $post->setLiked($post->getLiked()+1);
        } else {
            $post->setLiked(1);
        }
        $em->persist($post);
        $em->flush();
        return $this->redirectToRoute('app_post');
    }

    #[Route('/post/categorie/{id}', name:'app_post_categorie')]
    public function post_categorie(int $id,PostRepository $postRepository, Connection $connection)
    {   
        $sql = "SELECT * FROM post WHERE category_id = ?";

        $posts = $connection->fetchAllAssociative($sql, [$id]);
        // $posts = $postRepository->findByCategory($id);
        return $this->render('post/category.html.twig', [
            'posts' =>$posts
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show')]
    public function show(int $id, PostRepository $postRepository): Response
    {
 
        $post = $postRepository->find($id);
     
        return $this->render('post/show.html.twig', [
            'article' => $post
        ]);
    }
}
