<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;


class PostController extends AbstractController
{
    private string $uploadsDir;
    public function __construct(string $uploadsDir)
    {
        $this->uploadsDir = $uploadsDir;
    }

    #[Route('/', name: 'app_post')]
    public function index(
        PostRepository $postRepository,
        CategoryRepository $categoryRepositoryn,
        SessionInterface $session,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }
        $categories = $categoryRepositoryn->findAll();
        $session->set('categories', $categories);
        $posts = $postRepository->findBy([], ['createdAt' => 'desc']);

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $posts, /* tableau des résultats */
            $request->query->getInt('page', 1), /* Numéro de page */
            6 /* Limite d'éléments par page */
        );

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/post/new', name: 'app_post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('error', "Vous devez être connecté pour ajouter un article.");
            return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
        }
        $post = new Post();
        $post->setUser($user);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $imageFile = $form->get('imageFile')->getData();
            // dd($imageFile);
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                // dd($imageFile);
                try {
                    $imageFile->move(
                        $this->uploadsDir,
                        $newFilename
                    );
                    $post->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Le téléchargement de l'image a échoué, veuillez réessayer.");
                }
            } else {
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

    #[Route('/post/edit/{id}', name: 'app_post_edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('error', "Vous devez être connecté pour modifier un article.");
            return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
        }

        // Vérifier si l'utilisateur connecté est l'auteur de l'article
        if ($user !== $post->getUser()) {
            // Si l'utilisateur n'est pas l'auteur, vous pouvez rediriger ou afficher une erreur
            $this->addFlash('error', "Vous n'êtes pas autorisé à modifier cet article.");
            return $this->redirectToRoute('app_post'); // Redirection vers la liste des articles
        }

        // Création et traitement du formulaire
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Stocker le nom de l'ancienne image
                $oldImage = $post->getImage();
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->uploadsDir,
                        $newFilename
                    );
                    $post->setImage($newFilename);
                    // Supprimer l'ancienne image si elle existe et n'est pas une URL par défaut
                    if ($oldImage && !str_starts_with($oldImage, 'http') && file_exists($this->uploadsDir . '/' . $oldImage)) {
                        unlink($this->uploadsDir . '/' . $oldImage);
                    }
                } catch (FileException $e) {
                    $this->addFlash('error', "Le téléchargement de l'image a échoué, veuillez réessayer.");
                }
            } else {
                $post->setImage('https://placehold.co/600x400/orange/white');
            }

           

            $em->persist($post);
            $em->flush();

            $this->addFlash('cle1', "L'article " . $post->getTitle() . " a été modifié");

            return $this->redirectToRoute('app_post');
        }
        return $this->render('post/edit.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }


    #[Route('/post/delete/{id}', name: 'app_post_delete')]
    public function delete(Post $post, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', "Vous devez être connecté pour modifier un article.");
            return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
        }

        if ($post) {
            $postUser = $post->getUser();
            // dd($user, $postUser->getEmail());
            if ($user === $postUser) {
                $em->remove($post);
                $em->flush();
                $this->addFlash('warning', 'Un article a été supprimé');
            } else {
                $this->addFlash('error', "Vous n'avez pas la permission");
            }
        }

        return $this->redirectToRoute('app_post');
    }

    #[Route('/post/liked/{id}', name: 'app_post_liked')]
    public function liked(Post $post, EntityManagerInterface $em): Response
    {
        if ($post->getLiked()) {
            $post->setLiked($post->getLiked() + 1);
        } else {
            $post->setLiked(1);
        }
        $em->persist($post);
        $em->flush();
        return $this->redirectToRoute('app_post');
    }

    #[Route('/post/categorie/{id}', name: 'app_post_categorie')]
    public function post_categorie(Category $category, PostRepository $postRepository, Connection $connection)
    {
        // $sql = "SELECT * FROM post WHERE category_id = ?";
        // $posts = $connection->fetchAllAssociative($sql, [$id]);
        $posts = $postRepository->findBy(['category' => $category]);
        // $posts = $postRepository->findByCategory($id);
        return $this->render('post/category.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/post/show/{id}', name: 'app_post_show')]
    public function show(int $id, PostRepository $postRepository): Response
    {

        $post = $postRepository->find($id);

        return $this->render('post/show.html.twig', [
            'article' => $post
        ]);
    }
}
