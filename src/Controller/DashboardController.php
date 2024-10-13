<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['createdAt' => 'desc']);
        return $this->render('dashboard/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    
}
