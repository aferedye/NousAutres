<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VerifierController extends AbstractController
{
    #[Route('/verifier-pseudo', name: 'verifier_pseudo', methods: ['GET'])]
    public function verifierPseudo(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $pseudo = $request->query->get('pseudo');

        $existe = $utilisateurRepository->findOneBy(['pseudo' => $pseudo]);

        return new JsonResponse([
            'disponible' => $existe === null
        ]);
    }

    #[Route('/verifier-email', name: 'verifier_email', methods: ['GET'])]
    public function verifierEmail(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $email = $request->query->get('email');

        $existe = $utilisateurRepository->findOneBy(['email' => $email]);

        return new JsonResponse([
            'disponible' => $existe === null
        ]);
    }
};