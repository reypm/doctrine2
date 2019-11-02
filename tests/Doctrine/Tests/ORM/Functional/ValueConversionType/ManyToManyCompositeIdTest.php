<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\ValueConversionType;

use Doctrine\Tests\Models\ValueConversionType as Entity;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * The entities all use a custom type that converst the value as identifier(s).
 * {@see \Doctrine\Tests\DbalTypes\Rot13Type}
 *
 * Test that ManyToMany associations with composite id work correctly.
 *
 * @group DDC-3380
 */
class ManyToManyCompositeIdTest extends OrmFunctionalTestCase
{
    public function setUp() : void
    {
        $this->useModelSet('vct_manytomany_compositeid');

        parent::setUp();

        $inversed      = new Entity\InversedManyToManyCompositeIdEntity();
        $inversed->id1 = 'abc';
        $inversed->id2 = 'def';

        $owning      = new Entity\OwningManyToManyCompositeIdEntity();
        $owning->id3 = 'ghi';

        $inversed->associatedEntities->add($owning);
        $owning->associatedEntities->add($inversed);

        $this->em->persist($inversed);
        $this->em->persist($owning);

        $this->em->flush();
        $this->em->clear();
    }

    public function testThatTheValueOfIdentifiersAreConvertedInTheDatabase() : void
    {
        $conn = $this->em->getConnection();

        self::assertEquals('nop', $conn->fetchColumn('SELECT id1 FROM vct_inversed_manytomany_compositeid LIMIT 1'));
        self::assertEquals('qrs', $conn->fetchColumn('SELECT id2 FROM vct_inversed_manytomany_compositeid LIMIT 1'));

        self::assertEquals('tuv', $conn->fetchColumn('SELECT id3 FROM vct_owning_manytomany_compositeid LIMIT 1'));

        self::assertEquals('nop', $conn->fetchColumn('SELECT inversed_id1 FROM vct_xref_manytomany_compositeid LIMIT 1'));
        self::assertEquals('qrs', $conn->fetchColumn('SELECT inversed_id2 FROM vct_xref_manytomany_compositeid LIMIT 1'));
        self::assertEquals('tuv', $conn->fetchColumn('SELECT owning_id FROM vct_xref_manytomany_compositeid LIMIT 1'));
    }

    public function testThatEntitiesAreFetchedFromTheDatabase() : void
    {
        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdEntity::class,
            ['id1' => 'abc', 'id2' => 'def']
        );

        $owning = $this->em->find(Entity\OwningManyToManyCompositeIdEntity::class, 'ghi');

        self::assertInstanceOf(Entity\InversedManyToManyCompositeIdEntity::class, $inversed);
        self::assertInstanceOf(Entity\OwningManyToManyCompositeIdEntity::class, $owning);
    }

    public function testThatTheValueOfIdentifiersAreConvertedBackAfterBeingFetchedFromTheDatabase() : void
    {
        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdEntity::class,
            ['id1' => 'abc', 'id2' => 'def']
        );

        $owning = $this->em->find(Entity\OwningManyToManyCompositeIdEntity::class, 'ghi');

        self::assertEquals('abc', $inversed->id1);
        self::assertEquals('def', $inversed->id2);
        self::assertEquals('ghi', $owning->id3);
    }

    public function testThatTheCollectionFromOwningToInversedIsLoaded() : void
    {
        $owning = $this->em->find(
            Entity\OwningManyToManyCompositeIdEntity::class,
            'ghi'
        );

        self::assertCount(1, $owning->associatedEntities);
    }

    public function testThatTheCollectionFromInversedToOwningIsLoaded() : void
    {
        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdEntity::class,
            ['id1' => 'abc', 'id2' => 'def']
        );

        self::assertCount(1, $inversed->associatedEntities);
    }

    public function testThatTheJoinTableRowsAreRemovedWhenRemovingTheAssociation() : void
    {
        $conn = $this->em->getConnection();

        // remove association

        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdEntity::class,
            ['id1' => 'abc', 'id2' => 'def']
        );

        foreach ($inversed->associatedEntities as $owning) {
            $inversed->associatedEntities->removeElement($owning);
            $owning->associatedEntities->removeElement($inversed);
        }

        $this->em->flush();
        $this->em->clear();

        // test association is removed

        self::assertEquals(0, $conn->fetchColumn('SELECT COUNT(*) FROM vct_xref_manytomany_compositeid'));
    }
}
