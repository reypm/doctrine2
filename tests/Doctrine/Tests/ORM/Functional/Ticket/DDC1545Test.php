<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\Models\CMS\CmsArticle;
use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-1545
 */
class DDC1545Test extends OrmFunctionalTestCase
{
    private $articleId;

    private $userId;

    private $user2Id;

    public function setUp() : void
    {
        $this->useModelSet('cms');
        parent::setUp();
    }

    private function initDb($link)
    {
        $article        = new CmsArticle();
        $article->topic = 'foo';
        $article->text  = 'foo';

        $user           = new CmsUser();
        $user->status   = 'foo';
        $user->username = 'foo';
        $user->name     = 'foo';

        $user2           = new CmsUser();
        $user2->status   = 'bar';
        $user2->username = 'bar';
        $user2->name     = 'bar';

        if ($link) {
            $article->user = $user;
        }

        $this->em->persist($article);
        $this->em->persist($user);
        $this->em->persist($user2);
        $this->em->flush();
        $this->em->clear();

        $this->articleId = $article->id;
        $this->userId    = $user->id;
        $this->user2Id   = $user2->id;
    }

    public function testLinkObjects() : void
    {
        $this->initDb(false);

        // don't join association
        $article = $this->em->find(CmsArticle::class, $this->articleId);

        $user = $this->em->find(CmsUser::class, $this->userId);

        $article->user = $user;

        $this->em->flush();
        $this->em->clear();

        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        self::assertNotNull($article->user);
        self::assertEquals($user->id, $article->user->id);
    }

    public function testLinkObjectsWithAssociationLoaded() : void
    {
        $this->initDb(false);

        // join association
        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        $user = $this->em->find(CmsUser::class, $this->userId);

        $article->user = $user;

        $this->em->flush();
        $this->em->clear();

        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        self::assertNotNull($article->user);
        self::assertEquals($user->id, $article->user->id);
    }

    public function testUnlinkObjects() : void
    {
        $this->initDb(true);

        // don't join association
        $article = $this->em->find(CmsArticle::class, $this->articleId);

        $article->user = null;

        $this->em->flush();
        $this->em->clear();

        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        self::assertNull($article->user);
    }

    public function testUnlinkObjectsWithAssociationLoaded() : void
    {
        $this->initDb(true);

        // join association
        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        $article->user = null;

        $this->em->flush();
        $this->em->clear();

        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        self::assertNull($article->user);
    }

    public function testChangeLink() : void
    {
        $this->initDb(false);

        // don't join association
        $article = $this->em->find(CmsArticle::class, $this->articleId);

        $user2 = $this->em->find(CmsUser::class, $this->user2Id);

        $article->user = $user2;

        $this->em->flush();
        $this->em->clear();

        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        self::assertNotNull($article->user);
        self::assertEquals($user2->id, $article->user->id);
    }

    public function testChangeLinkWithAssociationLoaded() : void
    {
        $this->initDb(false);

        // join association
        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        $user2 = $this->em->find(CmsUser::class, $this->user2Id);

        $article->user = $user2;

        $this->em->flush();
        $this->em->clear();

        $article = $this->em
            ->createQuery('SELECT a, u FROM Doctrine\Tests\Models\Cms\CmsArticle a LEFT JOIN a.user u WHERE a.id = :id')
            ->setParameter('id', $this->articleId)
            ->getOneOrNullResult();

        self::assertNotNull($article->user);
        self::assertEquals($user2->id, $article->user->id);
    }
}
