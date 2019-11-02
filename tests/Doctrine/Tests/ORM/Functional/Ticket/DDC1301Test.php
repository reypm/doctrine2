<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Mapping\FetchMode;
use Doctrine\Tests\Models;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group non-cacheable
 * @group DDC-1301
 */
class DDC1301Test extends OrmFunctionalTestCase
{
    private $userId;

    public function setUp() : void
    {
        $this->useModelSet('legacy');

        parent::setUp();

        $class = $this->em->getClassMetadata(Models\Legacy\LegacyUser::class);

        $class->getProperty('articles')->setFetchMode(FetchMode::EXTRA_LAZY);
        $class->getProperty('references')->setFetchMode(FetchMode::EXTRA_LAZY);
        $class->getProperty('cars')->setFetchMode(FetchMode::EXTRA_LAZY);

        $this->loadFixture();
    }

    public function tearDown() : void
    {
        parent::tearDown();

        $class = $this->em->getClassMetadata(Models\Legacy\LegacyUser::class);

        $class->getProperty('articles')->setFetchMode(FetchMode::LAZY);
        $class->getProperty('references')->setFetchMode(FetchMode::LAZY);
        $class->getProperty('cars')->setFetchMode(FetchMode::LAZY);
    }

    public function testCountNotInitializesLegacyCollection() : void
    {
        $user       = $this->em->find(Models\Legacy\LegacyUser::class, $this->userId);
        $queryCount = $this->getCurrentQueryCount();

        self::assertFalse($user->articles->isInitialized());
        self::assertCount(2, $user->articles);
        self::assertFalse($user->articles->isInitialized());

        foreach ($user->articles as $article) {
        }

        self::assertEquals($queryCount + 2, $this->getCurrentQueryCount(), 'Expecting two queries to be fired for count, then iteration.');
    }

    public function testCountNotInitializesLegacyCollectionWithForeignIdentifier() : void
    {
        $user       = $this->em->find(Models\Legacy\LegacyUser::class, $this->userId);
        $queryCount = $this->getCurrentQueryCount();

        self::assertFalse($user->references->isInitialized());
        self::assertCount(2, $user->references);
        self::assertFalse($user->references->isInitialized());

        foreach ($user->references as $reference) {
        }

        self::assertEquals($queryCount + 2, $this->getCurrentQueryCount(), 'Expecting two queries to be fired for count, then iteration.');
    }

    public function testCountNotInitializesLegacyManyToManyCollection() : void
    {
        $user       = $this->em->find(Models\Legacy\LegacyUser::class, $this->userId);
        $queryCount = $this->getCurrentQueryCount();

        self::assertFalse($user->cars->isInitialized());
        self::assertCount(3, $user->cars);
        self::assertFalse($user->cars->isInitialized());

        foreach ($user->cars as $reference) {
        }

        self::assertEquals($queryCount + 2, $this->getCurrentQueryCount(), 'Expecting two queries to be fired for count, then iteration.');
    }

    public function loadFixture()
    {
        $user1           = new Models\Legacy\LegacyUser();
        $user1->username = 'beberlei';
        $user1->name     = 'Benjamin';
        $user1->status   = 'active';

        $user2           = new Models\Legacy\LegacyUser();
        $user2->username = 'jwage';
        $user2->name     = 'Jonathan';
        $user2->status   = 'active';

        $user3           = new Models\Legacy\LegacyUser();
        $user3->username = 'romanb';
        $user3->name     = 'Roman';
        $user3->status   = 'active';

        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->persist($user3);

        $article1        = new Models\Legacy\LegacyArticle();
        $article1->topic = 'Test';
        $article1->text  = 'Test';
        $article1->setAuthor($user1);

        $article2        = new Models\Legacy\LegacyArticle();
        $article2->topic = 'Test';
        $article2->text  = 'Test';
        $article2->setAuthor($user1);

        $this->em->persist($article1);
        $this->em->persist($article2);

        $car1              = new Models\Legacy\LegacyCar();
        $car1->description = 'Test1';

        $car2              = new Models\Legacy\LegacyCar();
        $car2->description = 'Test2';

        $car3              = new Models\Legacy\LegacyCar();
        $car3->description = 'Test3';

        $user1->addCar($car1);
        $user1->addCar($car2);
        $user1->addCar($car3);

        $user2->addCar($car1);
        $user3->addCar($car1);

        $this->em->persist($car1);
        $this->em->persist($car2);
        $this->em->persist($car3);

        $this->em->flush();

        $detail1 = new Models\Legacy\LegacyUserReference($user1, $user2, 'foo');
        $detail2 = new Models\Legacy\LegacyUserReference($user1, $user3, 'bar');

        $this->em->persist($detail1);
        $this->em->persist($detail2);

        $this->em->flush();
        $this->em->clear();

        $this->userId = $user1->getId();
    }
}
