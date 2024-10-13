<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    
    public function index(CategoryRepository $categoryRepositoryn, SessionInterface $session): Response
    {
        $categories = $categoryRepositoryn->findAll();
        $session->set('categories',$categories);
        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/category/new/{id}', name: 'app_category_new', defaults: ['id' => null])]
    // #[IsGranted('ROLE_ADMIN')]
    public function new(?Category $category,Request $request, EntityManagerInterface $em): Response
    {
        $operation = $category?'edit':'new'; 
        if (!$category) {

            $category = new Category();
        }
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/new.html.twig', [
            'formulaire' => $form->createView(),
            'operation' => $operation
        ]);
    }


}
