<?php

namespace App\Controller;

use App\Repository\CircleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CircleController extends AbstractController
{
    #[Route('/cercles', name: 'app_cercles')]
    public function index(CircleRepository $repo): Response
    {
        $cercles = $repo->findPublicActives(24);

        // map simple vers tableau pour Twig
        $arr = array_map(fn($c) => [
            'nom' => $c->getName(),
            'description' => $c->getDescription(),
        ], $cercles);

        return $this->render('cercles.html.twig', [
            'cercles' => $arr
        ]);
    }
}

