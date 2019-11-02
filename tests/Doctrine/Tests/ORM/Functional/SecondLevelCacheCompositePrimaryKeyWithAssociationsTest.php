<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\ORM\Cache;
use Doctrine\Tests\Models\GeoNames\Admin1;
use Doctrine\Tests\Models\GeoNames\Admin1AlternateName;
use Doctrine\Tests\Models\GeoNames\Country;
use Doctrine\Tests\OrmFunctionalTestCase;

class SecondLevelCacheCompositePrimaryKeyWithAssociationsTest extends OrmFunctionalTestCase
{
    /** @var Cache */
    protected $cache;

    public function setUp() : void
    {
        $this->enableSecondLevelCache();
        $this->useModelSet('geonames');
        parent::setUp();

        $this->cache = $this->em->getCache();

        $it = new Country('IT', 'Italy');

        $this->em->persist($it);
        $this->em->flush();

        $admin1 = new Admin1(1, 'Rome', $it);

        $this->em->persist($admin1);
        $this->em->flush();

        $name1 = new Admin1AlternateName(1, 'Roma', $admin1);
        $name2 = new Admin1AlternateName(2, 'Rome', $admin1);

        $admin1->names[] = $name1;
        $admin1->names[] = $name2;

        $this->em->persist($admin1);
        $this->em->persist($name1);
        $this->em->persist($name2);

        $this->em->flush();
        $this->em->clear();
        $this->evictRegions();
    }

    public function testFindByReturnsCachedEntity() : void
    {
        $admin1Repo = $this->em->getRepository(Admin1::class);

        $queries = $this->getCurrentQueryCount();

        $admin1Rome = $admin1Repo->findOneBy(['country' => 'IT', 'id' => 1]);

        self::assertEquals('Italy', $admin1Rome->country->name);
        self::assertCount(2, $admin1Rome->names);
        self::assertEquals($queries + 3, $this->getCurrentQueryCount());

        $this->em->clear();

        $queries = $this->getCurrentQueryCount();

        $admin1Rome = $admin1Repo->findOneBy(['country' => 'IT', 'id' => 1]);

        self::assertEquals('Italy', $admin1Rome->country->name);
        self::assertCount(2, $admin1Rome->names);
        self::assertEquals($queries, $this->getCurrentQueryCount());
    }

    private function evictRegions()
    {
        $this->cache->evictQueryRegions();
        $this->cache->evictEntityRegions();
        $this->cache->evictCollectionRegions();
    }
}
