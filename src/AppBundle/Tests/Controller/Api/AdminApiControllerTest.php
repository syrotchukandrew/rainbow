<?php

declare(strict_types=1);

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Tests\Controller\BaseTestController;

class AdminApiControllerTest extends BaseTestController
{
    public function testPendingCountRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/comments/pending-count');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testPendingCountReturnsCount(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/comments/pending-count');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('count', $data);
        $this->assertIsInt($data['count']);
    }

    public function testDistrictListRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/districts');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testDistrictListReturnsArray(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/districts');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('slug', $data[0]);
        $this->assertArrayHasKey('title', $data[0]);
    }

    public function testCreateDistrictRequiresAdmin(): void
    {
        // IsGranted('ROLE_ADMIN') fires before CSRF validation, so no valid token needed
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_manager2', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('POST', '/api/admin/districts', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => 'any-token',
        ], json_encode(['title' => 'Test District']));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testCreateDistrictRequiresValidCsrf(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('POST', '/api/admin/districts', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => 'invalid-token',
        ], json_encode(['title' => 'Test District']));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testCreateDistrictSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getCsrfToken($client);
        $client->request('POST', '/api/admin/districts', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => 'Test District Api']));
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Test District Api', $data['title']);
        $this->assertNotEmpty($data['slug']);

        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $district = $em->getRepository(\AppBundle\Entity\District::class)->find($data['id']);
        if ($district) {
            $em->remove($district);
            $em->flush();
        }
    }

    public function testCreateDistrictValidationError(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getCsrfToken($client);
        $client->request('POST', '/api/admin/districts', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => 'AB']));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdateDistrictSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $district = $em->getRepository(\AppBundle\Entity\District::class)->findOneBy([]);
        $originalTitle = $district->getTitle();
        $districtId = $district->getId();
        $slug = $district->getSlug();

        $csrf = $this->getCsrfToken($client);
        $client->request('PUT', "/api/admin/districts/{$slug}", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => $originalTitle . ' Updated']));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringEndsWith(' Updated', $data['title']);

        // Restore by ID since slug may have changed due to Gedmo Sluggable
        $em->clear();
        $district = $em->getRepository(\AppBundle\Entity\District::class)->find($districtId);
        $district->setTitle($originalTitle);
        $em->flush();
    }

    public function testUpdateDistrictNotFound(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getCsrfToken($client);
        $client->request('PUT', '/api/admin/districts/nonexistent-slug', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => 'Something']));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteDistrictSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $district = new \AppBundle\Entity\District();
        $district->setTitle('District To Delete');
        $em->persist($district);
        $em->flush();
        $slug = $district->getSlug();
        $em->clear();

        $csrf = $this->getCsrfToken($client);
        $client->request('DELETE', "/api/admin/districts/{$slug}", [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
        $deleted = $em->getRepository(\AppBundle\Entity\District::class)->findOneBy(['slug' => $slug]);
        $this->assertNull($deleted);
    }

    public function testCommentListRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/comments');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testCommentListReturnsPendingByDefault(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/comments');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        foreach ($data as $comment) {
            $this->assertFalse($comment['enabled']);
        }
    }

    public function testCommentListReturnsPublished(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/comments?status=published');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        foreach ($data as $comment) {
            $this->assertTrue($comment['enabled']);
        }
    }

    public function testCommentListReturnsAll(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/comments?status=all');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $enabled = array_column($data, 'enabled');
        $this->assertContains(true, $enabled);
        $this->assertContains(false, $enabled);
    }

    public function testApproveCommentRequiresAdmin(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_manager2', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $comment = $em->getRepository(\AppBundle\Entity\Comment::class)->findOneBy(['enabled' => false]);
        $client->request('POST', "/api/admin/comments/{$comment->getId()}/approve", [], [], [
            'HTTP_X-CSRF-Token' => 'any-token',
        ]);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testApproveCommentRequiresValidCsrf(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $comment = $em->getRepository(\AppBundle\Entity\Comment::class)->findOneBy(['enabled' => false]);
        $client->request('POST', "/api/admin/comments/{$comment->getId()}/approve", [], [], [
            'HTTP_X-CSRF-Token' => 'invalid-token',
        ]);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testApproveCommentSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $comment = $em->getRepository(\AppBundle\Entity\Comment::class)->findOneBy(['enabled' => false]);
        $commentId = $comment->getId();

        $csrf = $this->getCommentCsrfToken($client);
        $client->request('POST', "/api/admin/comments/{$commentId}/approve", [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['enabled']);

        // Restore
        $em->clear();
        $approved = $em->getRepository(\AppBundle\Entity\Comment::class)->find($commentId);
        $approved->setEnabled(false);
        $em->flush();
    }

    public function testApproveCommentNotFound(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getCommentCsrfToken($client);
        $client->request('POST', '/api/admin/comments/999999/approve', [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteCommentRequiresAdmin(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_manager2', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('DELETE', '/api/admin/comments/1', [], [], [
            'HTTP_X-CSRF-Token' => 'any-token',
        ]);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testDeleteCommentSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $comment = new \AppBundle\Entity\Comment();
        $comment->setContent('Comment to delete in test');
        $comment->setEnabled(false);
        $comment->setCreatedBy('user_admin');
        $comment->setCreatedAt(new \DateTime());
        $em->persist($comment);
        $em->flush();
        $commentId = $comment->getId();
        $em->clear();

        $csrf = $this->getCommentCsrfToken($client);
        $client->request('DELETE', "/api/admin/comments/{$commentId}", [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
        $deleted = $em->getRepository(\AppBundle\Entity\Comment::class)->find($commentId);
        $this->assertNull($deleted);
    }

    public function testUserListRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/users');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testUserListReturnsUsers(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/users');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('username', $data[0]);
        $this->assertArrayHasKey('email', $data[0]);
        $this->assertArrayHasKey('roles', $data[0]);
        $this->assertArrayHasKey('enabled', $data[0]);
        // ROLE_ADMIN users must be excluded
        foreach ($data as $user) {
            $this->assertNotContains('ROLE_ADMIN', $user['roles']);
        }
    }

    public function testLockUserRequiresAdmin(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_manager2', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('POST', '/api/admin/users/user_user1/lock', [], [], [
            'HTTP_X-CSRF-Token' => 'any-token',
        ]);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testLockAndUnlockUserSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getUserCsrfToken($client);

        $client->request('POST', '/api/admin/users/user_user1/lock', [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($data['enabled']);

        // Restore
        $client->request('POST', '/api/admin/users/user_user1/unlock', [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['enabled']);
    }

    public function testMakeManagerAndMakeUserSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getUserCsrfToken($client);

        $client->request('POST', '/api/admin/users/user_user1/make-manager', [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertContains('ROLE_MANAGER', $data['roles']);

        // Restore
        $client->request('POST', '/api/admin/users/user_user1/make-user', [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotContains('ROLE_MANAGER', $data['roles']);
    }

    public function testLockUserNotFound(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getUserCsrfToken($client);
        $client->request('POST', '/api/admin/users/nonexistent_user/lock', [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testMenuItemListRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/menu-items');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testMenuItemListReturnsArray(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/menu-items');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testCreateMenuItemRequiresAdmin(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_manager2', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('POST', '/api/admin/menu-items', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => 'any-token',
        ], json_encode(['title' => 'Test Item']));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testCreateMenuItemRequiresValidCsrf(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('POST', '/api/admin/menu-items', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => 'invalid-token',
        ], json_encode(['title' => 'Test Item']));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testCreateMenuItemSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getMenuItemCsrfToken($client);
        $client->request('POST', '/api/admin/menu-items', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => 'Test Menu Item Api', 'description' => 'Test desc']));
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Test Menu Item Api', $data['title']);
        $this->assertEquals('Test desc', $data['description']);

        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $item = $em->getRepository(\AppBundle\Entity\MenuItem::class)->find($data['id']);
        if ($item) {
            $em->remove($item);
            $em->flush();
        }
    }

    public function testCreateMenuItemValidationError(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getMenuItemCsrfToken($client);
        $client->request('POST', '/api/admin/menu-items', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => '']));
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdateMenuItemSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $item = $em->getRepository(\AppBundle\Entity\MenuItem::class)->findOneBy([]);
        $originalTitle = $item->getTitle();
        $itemId = $item->getId();

        $csrf = $this->getMenuItemCsrfToken($client);
        $client->request('PUT', "/api/admin/menu-items/{$itemId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => $originalTitle . ' Updated', 'description' => 'Updated desc']));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringEndsWith(' Updated', $data['title']);

        // Restore
        $em->clear();
        $restored = $em->getRepository(\AppBundle\Entity\MenuItem::class)->find($itemId);
        $restored->setTitle($originalTitle);
        $em->flush();
    }

    public function testUpdateMenuItemNotFound(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $csrf = $this->getMenuItemCsrfToken($client);
        $client->request('PUT', '/api/admin/menu-items/999999', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrf,
        ], json_encode(['title' => 'Something', 'description' => 'Some desc']));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteMenuItemSuccess(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $item = new \AppBundle\Entity\MenuItem();
        $item->setTitle('MenuItem To Delete');
        $em->persist($item);
        $em->flush();
        $itemId = $item->getId();
        $em->clear();

        $csrf = $this->getMenuItemCsrfToken($client);
        $client->request('DELETE', "/api/admin/menu-items/{$itemId}", [], [], [
            'HTTP_X-CSRF-Token' => $csrf,
        ]);

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
        $deleted = $em->getRepository(\AppBundle\Entity\MenuItem::class)->find($itemId);
        $this->assertNull($deleted);
    }

    public function testEstateListRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/estates');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testEstateListReturnsArray(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'user_admin', 'PHP_AUTH_PW' => 'qweasz']);
        $client->request('GET', '/api/admin/estates');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        if (!empty($data)) {
            $this->assertArrayHasKey('id', $data[0]);
            $this->assertArrayHasKey('title', $data[0]);
            $this->assertArrayHasKey('slug', $data[0]);
            $this->assertArrayHasKey('exclusive', $data[0]);
        }
    }

    private function getCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        // The districts page embeds the CSRF token in data-csrf;
        // extract from HTML to avoid SessionNotFoundException outside a request
        $crawler = $client->request('GET', '/admin/districts');
        return $crawler->filter('#react-admin-districts')->attr('data-csrf');
    }

    private function getCommentCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/admin/comments');
        return $crawler->filter('#react-admin-comments')->attr('data-csrf');
    }

    private function getUserCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/admin/users');
        return $crawler->filter('#react-admin-users')->attr('data-csrf');
    }

    private function getMenuItemCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/admin/menu_items');
        return $crawler->filter('#react-admin-menu-items')->attr('data-csrf');
    }
}
