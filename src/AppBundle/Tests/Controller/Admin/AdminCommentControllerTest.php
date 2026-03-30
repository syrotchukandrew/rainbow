<?php

declare(strict_types=1);

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class AdminCommentControllerTest extends BaseTestController
{
    public function testIndex()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW' => 'qweasz',
        ));
        $crawler = $client->request('GET', '/admin/comments');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testAllComments()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW' => 'qweasz',
        ));
        $crawler = $client->request('GET', '/admin/comments/all');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testPublishedComments()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW' => 'qweasz',
        ));
        $crawler = $client->request('GET', '/admin/comments/published');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testShowComment()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW' => 'qweasz',
        ));
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $id = $em
            ->getRepository(\AppBundle\Entity\Comment::class)
            ->findOneBy([])->getId();
        $crawler = $client->request('GET', "/admin/comment/show/{$id}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('table')
        );
    }

    public function testEnableComment()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW' => 'qweasz',
        ));
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);

        // Ensure there is at least one disabled comment regardless of prior test runs
        $anyComment = $em->getRepository(\AppBundle\Entity\Comment::class)->findOneBy([]);
        if ($anyComment->isEnabled()) {
            $anyComment->setEnabled(false);
            $em->flush();
            $em->clear();
        }

        $comments = $em
            ->getRepository(\AppBundle\Entity\Comment::class)
            ->getDisabledComments();
        $countCommentsBefore = count($comments);
        $client->request('GET', "/admin/comment_enable/{$comments[0]->getId()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $comments = $em
            ->getRepository(\AppBundle\Entity\Comment::class)
            ->getDisabledComments();
        $countCommentsAfter = count($comments);

        $this->assertEquals($countCommentsAfter, ($countCommentsBefore - 1));
    }

    public function testEnableCommentManager()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_manager2',
            'PHP_AUTH_PW' => 'qweasz',
        ));
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        // Use any comment — the 403 auth check fires before any state change
        $comment = $em->getRepository(\AppBundle\Entity\Comment::class)->findOneBy([]);

        $client->request('GET', "/admin/comment_enable/{$comment->getId()}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
