<?php

namespace App\Controller;

use App\Entity\Circle;
use App\Repository\CircleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/circles', name: 'api_circles_')]
class CircleApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(CircleRepository $repo): JsonResponse
    {
        $circles = $repo->findBy([], ['createdAt' => 'DESC'], 100);
        return $this->json(array_map(fn(Circle $c) => $c->toArray(), $circles));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Circle $circle): JsonResponse
    {
        return $this->json($circle->toArray());
    }

    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $req, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        if ($req->headers->get('Content-Type') === null || stripos($req->headers->get('Content-Type'), 'application/json') === false) {
            return $this->json(['error' => 'Content-Type must be application/json'], 415);
        }

        $data = json_decode($req->getContent() ?: '[]', true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $circle = new Circle();
        $circle->setName((string)($data['name'] ?? ''));
        $circle->setDescription(isset($data['description']) ? (string)$data['description'] : null);
        $circle->setVisibility((string)($data['visibility'] ?? 'public'));
        $circle->setStatus((string)($data['status'] ?? 'active'));
        if (!empty($data['slug'])) { $circle->setSlug((string)$data['slug']); }

        $errors = $validator->validate($circle);
        if (count($errors) > 0) {
            $errs = [];
            foreach ($errors as $e) { $errs[] = $e->getMessage(); }
            return $this->json(['errors' => $errs], 422);
        }

        $em->persist($circle);
        $em->flush();

        return $this->json($circle->toArray(), 201);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'patch', methods: ['PATCH', 'PUT'])]
    public function patch(Circle $circle, Request $req, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        if ($req->headers->get('Content-Type') === null || stripos($req->headers->get('Content-Type'), 'application/json') === false) {
            return $this->json(['error' => 'Content-Type must be application/json'], 415);
        }

        $data = json_decode($req->getContent() ?: '[]', true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        if (array_key_exists('name', $data)) { $circle->setName((string)$data['name']); }
        if (array_key_exists('description', $data)) { $circle->setDescription($data['description'] !== null ? (string)$data['description'] : null); }
        if (array_key_exists('visibility', $data)) { $circle->setVisibility((string)$data['visibility']); }
        if (array_key_exists('status', $data)) { $circle->setStatus((string)$data['status']); }
        if (array_key_exists('slug', $data) && $data['slug']) { $circle->setSlug((string)$data['slug']); }

        $errors = $validator->validate($circle);
        if (count($errors) > 0) {
            $errs = [];
            foreach ($errors as $e) { $errs[] = $e->getMessage(); }
            return $this->json(['errors' => $errs], 422);
        }

        $em->flush();
        return $this->json($circle->toArray());
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Circle $circle, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($circle);
        $em->flush();
        return $this->json(null, 204);
    }
}
