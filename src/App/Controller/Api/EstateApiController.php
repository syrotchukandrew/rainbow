<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\Estate;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/estate')]
class EstateApiController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/{slug}/favorite', name: 'api_estate_add_favorite', methods: ['POST'])]
    public function addFavorite(
        #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('api_estate_favorite', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->hasEstate($estate)) {
            $user->addEstate($estate);
            $this->doctrine->getManager()->flush();
        }

        return new JsonResponse(['favorited' => true]);
    }

    #[Route('/{slug}/favorite', name: 'api_estate_remove_favorite', methods: ['DELETE'])]
    public function removeFavorite(
        #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('api_estate_favorite', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = $this->getUser();
        if ($user->hasEstate($estate)) {
            $user->removeEstate($estate);
            $this->doctrine->getManager()->flush();
        }

        return new JsonResponse(['favorited' => false]);
    }

    #[Route('/{slug}/comment', name: 'api_estate_post_comment', methods: ['POST'])]
    public function postComment(
        #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('api_estate_comment', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $content = trim((string) ($data['content'] ?? ''));

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setEstate($estate);

        $errors = $this->validator->validate($comment);
        if (count($errors) > 0) {
            return new JsonResponse(
                ['error' => $errors->get(0)->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $em = $this->doctrine->getManager();
        $em->persist($comment);
        $em->flush();

        return new JsonResponse(['success' => true, 'pending' => true], Response::HTTP_CREATED);
    }
}
