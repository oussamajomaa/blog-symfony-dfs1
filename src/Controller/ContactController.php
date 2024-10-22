<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EmailService $emailService): Response
    {
        // Créer le formulaire de contact
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactData = $form->getData();

            // Préparer l'e-mail
            $emailService->sendEmail(
                $contactData['email'],
                $_ENV['EMAIL_SENDER'],
                'Message de contact',
                
                    '<p>Nom : '.$contactData['name']. "</p>".
                    '<p>Email : '.$contactData['email']."</p>".
                    '<p>Message : '.$contactData['message']."</p>"
            );
            
            // Ajouter un message flash de succès
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');

            // Redirection après soumission réussie
            return $this->redirectToRoute('app_contact');
        }

        // Afficher le formulaire de contact
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
