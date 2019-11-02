<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\ORM\Mapping\FetchMode;
use Doctrine\Tests\Models\Cache\City;
use Doctrine\Tests\Models\Cache\State;
use Doctrine\Tests\Models\Cache\Travel;

/**
 * @group DDC-2183
 */
class SecondLevelCacheExtraLazyCollectionTest extends SecondLevelCacheAbstractTest
{
    public function setUp() : void
    {
        parent::setUp();

        $sourceEntity = $this->em->getClassMetadata(Travel::class);
        $targetEntity = $this->em->getClassMetadata(City::class);

        $sourceEntity->getProperty('visitedCities')->setFetchMode(FetchMode::EXTRA_LAZY);
        $targetEntity->getProperty('travels')->setFetchMode(FetchMode::EXTRA_LAZY);
    }

    public function tearDown() : void
    {
        parent::tearDown();

        $sourceEntity = $this->em->getClassMetadata(Travel::class);
        $targetEntity = $this->em->getClassMetadata(City::class);

        $sourceEntity->getProperty('visitedCities')->setFetchMode(FetchMode::LAZY);
        $targetEntity->getProperty('travels')->setFetchMode(FetchMode::LAZY);
    }

    public function testCacheCountAfterAddThenFlush() : void
    {
        $this->loadFixturesCountries();
        $this->loadFixturesStates();
        $this->loadFixturesCities();
        $this->loadFixturesTraveler();
        $this->loadFixturesTravels();

        $this->em->clear();

        $ownerId = $this->travels[0]->getId();
        $owner   = $this->em->find(Travel::class, $ownerId);
        $ref     = $this->em->find(State::class, $this->states[1]->getId());

        self::assertTrue($this->cache->containsEntity(Travel::class, $ownerId));
        self::assertTrue($this->cache->containsCollection(Travel::class, 'visitedCities', $ownerId));

        $newItem = new City('New City', $ref);
        $owner->getVisitedCities()->add($newItem);

        $this->em->persist($newItem);
        $this->em->persist($owner);

        $queryCount = $this->getCurrentQueryCount();

        self::assertFalse($owner->getVisitedCities()->isInitialized());
        self::assertEquals(4, $owner->getVisitedCities()->count());
        self::assertFalse($owner->getVisitedCities()->isInitialized());
        self::assertEquals($queryCount, $this->getCurrentQueryCount());

        $this->em->flush();

        self::assertFalse($owner->getVisitedCities()->isInitialized());
        self::assertFalse($this->cache->containsCollection(Travel::class, 'visitedCities', $ownerId));

        $this->em->clear();

        $queryCount = $this->getCurrentQueryCount();
        $owner      = $this->em->find(Travel::class, $ownerId);

        self::assertEquals(4, $owner->getVisitedCities()->count());
        self::assertFalse($owner->getVisitedCities()->isInitialized());
        self::assertEquals($queryCount + 1, $this->getCurrentQueryCount());
    }
}
