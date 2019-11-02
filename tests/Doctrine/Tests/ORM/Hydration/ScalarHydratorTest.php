<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Hydration;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Internal\Hydration\ScalarHydrator;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Tests\Mocks\HydratorMockStatement;
use Doctrine\Tests\Models\CMS\CmsUser;

class ScalarHydratorTest extends HydrationTestCase
{
    /**
     * Select u.id, u.name from CmsUser u
     */
    public function testNewHydrationSimpleEntityQuery() : void
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(CmsUser::class, 'u');
        $rsm->addFieldResult('u', 'u__id', 'id');
        $rsm->addFieldResult('u', 'u__name', 'name');

        // Faked result set
        $resultSet = [
            [
                'u__id' => '1',
                'u__name' => 'romanb',
            ],
            [
                'u__id' => '2',
                'u__name' => 'jwage',
            ],
        ];

        $stmt     = new HydratorMockStatement($resultSet);
        $hydrator = new ScalarHydrator($this->em);

        $result = $hydrator->hydrateAll($stmt, $rsm);

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);
        self::assertEquals('romanb', $result[0]['u_name']);
        self::assertEquals(1, $result[0]['u_id']);
        self::assertEquals('jwage', $result[1]['u_name']);
        self::assertEquals(2, $result[1]['u_id']);
    }

    /**
     * @group DDC-407
     */
    public function testHydrateScalarResults() : void
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('foo1', 'foo', Type::getType('string'));
        $rsm->addScalarResult('bar2', 'bar', Type::getType('string'));
        $rsm->addScalarResult('baz3', 'baz', Type::getType('string'));

        $resultSet = [
            [
                'foo1' => 'A',
                'bar2' => 'B',
                'baz3' => 'C',
            ],
        ];

        $stmt     = new HydratorMockStatement($resultSet);
        $hydrator = new ScalarHydrator($this->em);

        self::assertCount(1, $hydrator->hydrateAll($stmt, $rsm));
    }

    /**
     * @group DDC-644
     */
    public function testSkipUnknownColumns() : void
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(CmsUser::class, 'u');
        $rsm->addFieldResult('u', 'u__id', 'id');
        $rsm->addFieldResult('u', 'u__name', 'name');
        $rsm->addScalarResult('foo1', 'foo', Type::getType('string'));
        $rsm->addScalarResult('bar2', 'bar', Type::getType('string'));
        $rsm->addScalarResult('baz3', 'baz', Type::getType('string'));

        $resultSet = [
            [
                'u__id' => '1',
                'u__name' => 'romanb',
                'foo1' => 'A',
                'bar2' => 'B',
                'baz3' => 'C',
                'foo' => 'bar', // Unknown!
            ],
        ];

        $stmt     = new HydratorMockStatement($resultSet);
        $hydrator = new ScalarHydrator($this->em);

        self::assertCount(1, $hydrator->hydrateAll($stmt, $rsm));
    }
}
