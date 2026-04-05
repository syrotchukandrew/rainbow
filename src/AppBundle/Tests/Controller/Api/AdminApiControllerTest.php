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

    private function getCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        // The districts page embeds the CSRF token in data-csrf;
        // extract from HTML to avoid SessionNotFoundException outside a request
        $crawler = $client->request('GET', '/admin/districts');
        return $crawler->filter('#react-admin-districts')->attr('data-csrf');
    }
}
