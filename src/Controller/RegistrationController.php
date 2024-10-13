<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\EmailService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;



class RegistrationController extends AbstractController
{
    private $emailService;
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérifier si l'utilisateur est connecté et son rôle
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Générer un token de confirmation unique
            $token = Uuid::v4()->toRfc4122();
            $user->setToken($token);

            // Si l'utilisateur est admin, récupérer le rôle sélectionné
            if ($isAdmin) {
                $selectedRole = $request->request->get('role');
                if ($selectedRole) {
                    $user->setRoles([$selectedRole]); // Assurez-vous de le mettre dans un tableau
                }
            }

            

            $entityManager->persist($user);
            $entityManager->flush();

            // générer un lien absolu
            $confirmationLink = $this->generateUrl('app_confirm_email', [
                'token' => $token,
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            // Envoyer l'email avec le lien de confirmation
            $this->emailService->sendEmail(
                $user->getEmail(),
                'Veuillez confirmer votre inscription',
                'Merci de vous être inscrit. 
                Veuillez confirmer votre compte en cliquant sur ce lien : 
                <a href="' . $confirmationLink . '">Confirmer mon compte</a>'
            );

            // Rediriger l'utilisateur après l'inscription
            return $this->redirectToRoute('app_post');
        }

        // Choisir le template de base en fonction du rôle
        $template = $isAdmin ? 'base_admin.html.twig' : 'base.html.twig';

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'isAdmin' => $isAdmin,
            'template' => $template
        ]);
    }
}
