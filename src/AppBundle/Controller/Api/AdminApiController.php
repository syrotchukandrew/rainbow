<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Category;
use AppBundle\Entity\Comment;
use AppBundle\Entity\District;
use AppBundle\Entity\Estate;
use AppBundle\Entity\MenuItem;
use AppBundle\Entity\User;
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

    #[Route('/comments', name: 'api_admin_comments_list', methods: ['GET'])]
    public function commentList(Request $request): JsonResponse
    {
        $status = $request->query->getString('status', 'pending');
        $repo = $this->doctrine->getRepository(Comment::class);

        $comments = match ($status) {
            'published' => $repo->getEnabledComments(),
            'all'       => $repo->findAllComments(),
            default     => $repo->getDisabledComments(),
        };

        return new JsonResponse(array_map(
            static fn(Comment $c) => [
                'id'        => $c->getId(),
                'content'   => $c->getContent(),
                'createdBy' => $c->getCreatedBy(),
                'createdAt' => $c->getCreatedAt()?->format('d.m.Y H:i'),
                'enabled'   => $c->isEnabled(),
                'estateId'  => $c->getEstate()?->getId(),
            ],
            $comments,
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/comments/{id}/approve', name: 'api_admin_comments_approve', methods: ['POST'])]
    public function commentApprove(int $id, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_comment', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $comment = $this->doctrine->getRepository(Comment::class)->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $comment->setEnabled(true);
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['id' => $comment->getId(), 'enabled' => $comment->isEnabled()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/comments/{id}', name: 'api_admin_comments_delete', methods: ['DELETE'])]
    public function commentDelete(int $id, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_comment', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $comment = $this->doctrine->getRepository(Comment::class)->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $em = $this->doctrine->getManager();
        $em->remove($comment);
        $em->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/users', name: 'api_admin_users_list', methods: ['GET'])]
    public function userList(): JsonResponse
    {
        $users = $this->doctrine->getRepository(User::class)->findAll();

        $data = array_values(array_map(
            static fn(User $u) => [
                'id'        => $u->getId(),
                'username'  => $u->getUsername(),
                'email'     => $u->getEmail(),
                'roles'     => $u->getRoles(),
                'enabled'   => $u->isEnabled(),
                'locked'    => !$u->isAccountNonLocked(),
                'lastLogin' => $u->getLastLogin()?->format('d.m.Y H:i'),
            ],
            array_filter($users, static fn(User $u) => !in_array('ROLE_ADMIN', $u->getRoles(), true)),
        ));

        return new JsonResponse($data);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/{username}/lock', name: 'api_admin_users_lock', methods: ['POST'])]
    public function userLock(string $username, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_user', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $user->setEnabled(false);
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['username' => $user->getUsername(), 'enabled' => $user->isEnabled()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/{username}/unlock', name: 'api_admin_users_unlock', methods: ['POST'])]
    public function userUnlock(string $username, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_user', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $user->setEnabled(true);
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['username' => $user->getUsername(), 'enabled' => $user->isEnabled()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/{username}/make-manager', name: 'api_admin_users_make_manager', methods: ['POST'])]
    public function userMakeManager(string $username, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_user', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $user->addRole('ROLE_MANAGER');
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['username' => $user->getUsername(), 'roles' => $user->getRoles()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/{username}/make-user', name: 'api_admin_users_make_user', methods: ['POST'])]
    public function userMakeUser(string $username, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_user', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $user->removeRole('ROLE_MANAGER');
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['username' => $user->getUsername(), 'roles' => $user->getRoles()]);
    }

    #[Route('/menu-items', name: 'api_admin_menu_items_list', methods: ['GET'])]
    public function menuItemList(): JsonResponse
    {
        $items = $this->doctrine->getRepository(MenuItem::class)->findAll();

        return new JsonResponse(array_map(
            static fn(MenuItem $m) => ['id' => $m->getId(), 'title' => $m->getTitle(), 'description' => $m->getDescription()],
            $items,
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/menu-items', name: 'api_admin_menu_items_create', methods: ['POST'])]
    public function menuItemCreate(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_menu_item', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $item = new MenuItem();
        $item->setTitle(trim((string) ($data['title'] ?? '')));
        $item->setDescription(trim((string) ($data['description'] ?? '')));

        $errors = $this->validator->validate($item);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => $errors[0]->getMessage()], 422);
        }

        $em = $this->doctrine->getManager();
        $em->persist($item);
        $em->flush();

        return new JsonResponse(['id' => $item->getId(), 'title' => $item->getTitle(), 'description' => $item->getDescription()], 201);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/menu-items/{id}', name: 'api_admin_menu_items_update', methods: ['PUT'])]
    public function menuItemUpdate(int $id, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_menu_item', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $item = $this->doctrine->getRepository(MenuItem::class)->find($id);
        if (!$item) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $item->setTitle(trim((string) ($data['title'] ?? '')));
        $item->setDescription(trim((string) ($data['description'] ?? '')));

        $errors = $this->validator->validate($item);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => $errors[0]->getMessage()], 422);
        }

        $this->doctrine->getManager()->flush();

        return new JsonResponse(['id' => $item->getId(), 'title' => $item->getTitle(), 'description' => $item->getDescription()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/menu-items/{id}', name: 'api_admin_menu_items_delete', methods: ['DELETE'])]
    public function menuItemDelete(int $id, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('api_admin_menu_item', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $item = $this->doctrine->getRepository(MenuItem::class)->find($id);
        if (!$item) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $em = $this->doctrine->getManager();
        $em->remove($item);
        $em->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/estates', name: 'api_admin_estates_list', methods: ['GET'])]
    public function estateList(Request $request): JsonResponse
    {
        $estates = $this->doctrine->getRepository(Estate::class)->getEstatesWithAll();

        return new JsonResponse(array_map(
            fn(Estate $e) => [
                'id'        => $e->getId(),
                'title'     => $e->getTitle(),
                'slug'      => $e->getSlug(),
                'category'  => $e->getCategory()?->getTitle(),
                'price'     => $e->getPrice(),
                'createdAt' => $e->getCreatedAt()?->format('d.m.Y'),
                'district'  => $e->getDistrict()?->getTitle(),
                'exclusive' => $e->isExclusive(),
                'showUrl'   => $this->generateUrl('admin_estate_show', ['slug' => $e->getSlug()]),
                'editUrl'   => $this->generateUrl('admin_estate_edit', ['slug' => $e->getSlug()]),
            ],
            $estates,
        ));
    }

    #[Route('/categories', name: 'api_admin_categories_list', methods: ['GET'])]
    public function categoryList(): JsonResponse
    {
        $categories = $this->doctrine->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->orderBy('c.root', 'ASC')
            ->addOrderBy('c.lft', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_map(
            fn(Category $c) => [
                'id'        => $c->getId(),
                'slug'      => $c->getSlug(),
                'title'     => $c->getTitle(),
                'lvl'       => $c->getLvl(),
                'upUrl'     => $c->getLvl() > 0 ? $this->generateUrl('admin_category_up', ['slug' => $c->getSlug()]) : null,
                'downUrl'   => $c->getLvl() > 0 ? $this->generateUrl('admin_category_down', ['slug' => $c->getSlug()]) : null,
                'editUrl'   => $this->generateUrl('admin_category_edit', ['slug' => $c->getSlug()]),
                'deleteUrl' => $this->generateUrl('admin_category_delete', ['slug' => $c->getSlug()]),
            ],
            $categories,
        ));
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
