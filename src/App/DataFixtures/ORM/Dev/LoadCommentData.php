<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM\Dev;

use App\Entity\Comment;
use App\Entity\Estate;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCommentData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $estates = $manager->getRepository(Estate::class)->findAll();

        if (empty($estates)) {
            return;
        }

        foreach (array_slice($estates, 0, 10) as $estate) {
            // 3 disabled comments per estate for moderation tests
            for ($i = 0; $i < 3; $i++) {
                $comment = new Comment();
                $comment->setEstate($estate);
                $comment->setContent('Test comment ' . $i . ' for estate ' . $estate->getId());
                $comment->setEnabled(false);
                $comment->setCreatedBy('user_user1');
                $comment->setCreatedAt(new \DateTime());
                $manager->persist($comment);
            }

            // 2 enabled comments per estate
            for ($i = 0; $i < 2; $i++) {
                $comment = new Comment();
                $comment->setEstate($estate);
                $comment->setContent('Approved comment ' . $i . ' for estate ' . $estate->getId());
                $comment->setEnabled(true);
                $comment->setCreatedBy('user_user1');
                $comment->setCreatedAt(new \DateTime());
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 6;
    }
}