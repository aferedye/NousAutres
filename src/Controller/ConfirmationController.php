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
    #[Route('/confirmation', name: 'app_confirmation')]
    public function confirmer(
        Request $request,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Tu dois être connecté pour confirmer ton compte.');
            return $this->redirectToRoute('app_login');
        }

        $codeSaisi = $request->request->get('code');

        if ($request->isMethod('POST')) {
            if ($user->getConfirmationCode() === $codeSaisi) {
                $user->setIsVerified(true);
                $user->setConfirmationCode(null);
                $entityManager->flush();

                $this->addFlash('success', 'Ton compte est maintenant activé.');
                return $this->redirectToRoute('app_login'); // ou autre route d’accueil
            } else {
                $this->addFlash('error', 'Code invalide. Vérifie ton e-mail.');
            }
        }

        return $this->render('registration/confirmation.html.twig');
    }
}