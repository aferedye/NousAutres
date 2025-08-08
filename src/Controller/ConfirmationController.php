<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfirmationController extends AbstractController
{
    #[Route('/confirmation', name: 'app_confirm_account')]
    public function confirm(Request $request, UtilisateurRepository $utilisateurRepo, EntityManagerInterface $em): Response
    {
        $email = $request->query->get('email');
        $user = $utilisateurRepo->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_register');
        }

        if ($request->isMethod('POST')) {
            $codeSaisi = $request->request->get('confirmation_code');
            if ($codeSaisi == $user->getConfirmationCode()) {
                $user->setConfirmationCode(null); // ou flag "isVerified" true
                $em->flush();

                $this->addFlash('success', 'Votre compte a été confirmé.');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('error', 'Code incorrect.');
        }

        return $this->render('registration/confirm_account.html.twig', [
            'email' => $email
        ]);
    }
}