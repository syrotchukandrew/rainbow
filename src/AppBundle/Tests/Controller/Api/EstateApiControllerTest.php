<?php

declare(strict_types=1);

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Entity\Estate;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EstateApiControllerTest extends WebTestCase
{
    public function testAddFavoriteRequiresAuthentication(): void
    {
        $client = static::createClient();
        $slug = $this->getEstateSlug($client);
        $client->request('POST', "/api/estate/{$slug}/favorite", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => 'invalid',
        ]);
        $this->assertResponseRedirects();
    }

    public function testAddFavoriteReturnsFavoritedTrue(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $slug = $this->getEstateSlug($client);
        $csrfToken = $this->getCsrfToken($client, 'api_estate_favorite');

        $client->request('POST', "/api/estate/{$slug}/favorite", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrfToken,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['favorited']);
    }

    public function testRemoveFavoriteReturnsFavoritedFalse(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $slug = $this->getEstateSlug($client);
        $csrfToken = $this->getCsrfToken($client, 'api_estate_favorite');

        // First add it
        $client->request('POST', "/api/estate/{$slug}/favorite", [], [], [
            'HTTP_X-CSRF-Token' => $csrfToken,
        ]);

        // Then remove it
        $client->request('DELETE', "/api/estate/{$slug}/favorite", [], [], [
            'HTTP_X-CSRF-Token' => $csrfToken,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($data['favorited']);
    }

    public function testAddFavoriteRejectsInvalidCsrf(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $slug = $this->getEstateSlug($client);

        $client->request('POST', "/api/estate/{$slug}/favorite", [], [], [
            'HTTP_X-CSRF-Token' => 'bad-token',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testPostCommentRequiresAuthentication(): void
    {
        $client = static::createClient();
        $slug = $this->getEstateSlug($client);
        $client->request('POST', "/api/estate/{$slug}/comment", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['content' => 'Hello']));
        $this->assertResponseRedirects();
    }

    public function testPostCommentReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $slug = $this->getEstateSlug($client);
        $csrfToken = $this->getCsrfToken($client, 'api_estate_comment');

        $client->request('POST', "/api/estate/{$slug}/comment", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrfToken,
        ], json_encode(['content' => 'This is a test comment from API']));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function testPostCommentValidatesBlankContent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $slug = $this->getEstateSlug($client);
        $csrfToken = $this->getCsrfToken($client, 'api_estate_comment');

        $client->request('POST', "/api/estate/{$slug}/comment", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrfToken,
        ], json_encode(['content' => '']));

        $this->assertResponseStatusCodeSame(422);
    }

    private function getTestUser($client): User
    {
        $em = $client->getContainer()->get('doctrine')->getManager();
        return $em->getRepository(User::class)->findOneBy(['username' => 'user_user0']);
    }

    private function getEstateSlug($client): string
    {
        $em = $client->getContainer()->get('doctrine')->getManager();
        return $em->getRepository(Estate::class)->findOneBy([])->getSlug();
    }

    private function getCsrfToken($client, string $tokenId): string
    {
        $slug = $this->getEstateSlug($client);
        $client->request('GET', "/en/show_estate/{$slug}");
        $session = $client->getRequest()->getSession();
        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = $client->getContainer()->get('request_stack');
        $requestStack->push($client->getRequest());
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken($tokenId)->getValue();
        $requestStack->pop();
        $session->save();
        return $token;
    }
}
