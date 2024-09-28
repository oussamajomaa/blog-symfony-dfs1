<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(PostRepository $repo): Response
    {
        $posts = $repo->findAll();
        dump($posts);
        return $this->render('home/index.html.twig', [
            'posts' => $posts
        ]);
    }
}
