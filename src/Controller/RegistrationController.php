<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginFormAuthenticator;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Uid\Uuid;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        TransportInterface $mailer,
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

             // Envoi de l'e-mail de confirmation
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@nousautres.org', 'Nous Autres'))
                ->to($user->getEmail())
                ->subject('Votre code de confirmation')
                ->htmlTemplate('emails/confirmation_code.html.twig')
                ->context([
                    'pseudo' => $user->getPseudo(),
                    'confirmationCode' => $code,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Pour activer ton compte, clique sur le lien contenu dans le mail et entre le code.');
            $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );

        return $this->redirectToRoute('app_login');

        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}