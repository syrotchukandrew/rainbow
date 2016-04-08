<?php

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
        $crawler = $client->request('GET', '/ru/admin/comments');

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
        $crawler = $client->request('GET', '/ru/admin/comments/all');

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
        $crawler = $client->request('GET', '/ru/admin/comments/published');

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
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $id = $em
            ->getRepository('AppBundle:Comment')
            ->findOneBy([])->getId();
        $crawler = $client->request('GET', "/ru/admin/comment/show/{$id}");

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
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $comments = $em
            ->getRepository('AppBundle:Comment')
            ->getDisabledComments();
        $countCommentsBefore = count($comments);
        $client->request('GET', "/ru/admin/comment_enable/{$comments[0]->getId()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $comments = $em
            ->getRepository('AppBundle:Comment')
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
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $comments = $em
            ->getRepository('AppBundle:Comment')
            ->getDisabledComments();

        $client->request('GET', "/ru/admin/comment_enable/{$comments[0]->getId()}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}