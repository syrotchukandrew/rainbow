<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Comment;
use AppBundle\Entity\District;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_MANAGER')]
#[Route('/api/admin')]
class AdminApiController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/comments/pending-count', name: 'api_admin_comments_pending_count', methods: ['GET'])]
    public function pendingCount(): JsonResponse
    {
        $comments = $this->doctrine
            ->getRepository(Comment::class)
            ->getDisabledComments();

        return new JsonResponse(['count' => count($comments)]);
    }

    #[Route('/districts', name: 'api_admin_districts_list', methods: ['GET'])]
    public function districtList(): JsonResponse
    {
        $districts = $this->doctrine->getRepository(District::class)->findAll();

        return new JsonResponse(array_map(
            static fn(District $d) => ['id' => $d->getId(), 'slug' => $d->getSlug(), 'title' => $d->getTitle()],
            $districts,
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/districts', name: 'api_admin_districts_create', methods: ['POST'])]
    public function districtCreate(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_district', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $title = trim((string) ($data['title'] ?? ''));

        $district = new District();
        $district->setTitle($title);

        $errors = $this->validator->validate($district);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => $errors[0]->getMessage()], 422);
        }

        $em = $this->doctrine->getManager();
        $em->persist($district);
        $em->flush();

        return new JsonResponse(['id' => $district->getId(), 'slug' => $district->getSlug(), 'title' => $district->getTitle()], 201);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/districts/{slug}', name: 'api_admin_districts_update', methods: ['PUT'])]
    public function districtUpdate(string $slug, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_district', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $district = $this->doctrine->getRepository(District::class)->findOneBy(['slug' => $slug]);
        if (!$district) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $title = trim((string) ($data['title'] ?? ''));

        $district->setTitle($title);

        $errors = $this->validator->validate($district);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => $errors[0]->getMessage()], 422);
        }

        $this->doctrine->getManager()->flush();

        return new JsonResponse(['id' => $district->getId(), 'slug' => $district->getSlug(), 'title' => $district->getTitle()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/districts/{slug}', name: 'api_admin_districts_delete', methods: ['DELETE'])]
    public function districtDelete(string $slug, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_district', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $district = $this->doctrine->getRepository(District::class)->findOneBy(['slug' => $slug]);
        if (!$district) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $em = $this->doctrine->getManager();
        $em->remove($district);
        $em->flush();

        return new JsonResponse(null, 204);
    }
}
