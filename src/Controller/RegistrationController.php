<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginFormAuthenticator;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator
    ): Response {
        $user = new Utilisateur();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            // Définir le rôle de base
            $user->setRoles(['ROLE_CITOYEN']);

            // Générer un code de confirmation
            $code = random_int(100000, 999999);
            $user->setConfirmationCode((string) $code);
            $user->setIsVerified(false);
            $user->setDateInscription(new \DateTimeImmutable());

            // Enregistrer en base
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoyer l'e-mail
            $email = (new Email())
                ->from('noreply@nous-autres.org')
                ->to($user->getEmail())
                ->subject('Confirmation d’inscription – Nous Autres')
                ->html("<p>Merci pour ton inscription.</p><p>Voici ton code de confirmation : <strong>$code</strong></p>");

            $mailer->send($email);

            $this->addFlash('success', 'Un code de confirmation t’a été envoyé par e-mail.');
            $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );

        return $this->redirectToRoute('app_confirmation');

        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}