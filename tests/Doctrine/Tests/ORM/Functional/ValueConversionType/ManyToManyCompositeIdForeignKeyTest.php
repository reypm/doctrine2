<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\ValueConversionType;

use Doctrine\Tests\Models\ValueConversionType as Entity;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * The entities all use a custom type that converst the value as identifier(s).
 * {@see \Doctrine\Tests\DbalTypes\Rot13Type}
 *
 * Test that ManyToMany associations with composite id of which one is a
 * association itself work correctly.
 *
 * @group DDC-3380
 */
class ManyToManyCompositeIdForeignKeyTest extends OrmFunctionalTestCase
{
    public function setUp() : void
    {
        $this->useModelSet('vct_manytomany_compositeid_foreignkey');

        parent::setUp();

        $auxiliary      = new Entity\AuxiliaryEntity();
        $auxiliary->id4 = 'abc';

        $inversed                = new Entity\InversedManyToManyCompositeIdForeignKeyEntity();
        $inversed->id1           = 'def';
        $inversed->foreignEntity = $auxiliary;

        $owning      = new Entity\OwningManyToManyCompositeIdForeignKeyEntity();
        $owning->id2 = 'ghi';

        $inversed->associatedEntities->add($owning);
        $owning->associatedEntities->add($inversed);

        $this->em->persist($auxiliary);
        $this->em->persist($inversed);
        $this->em->persist($owning);

        $this->em->flush();
        $this->em->clear();
    }

    public function testThatTheValueOfIdentifiersAreConvertedInTheDatabase() : void
    {
        $conn = $this->em->getConnection();

        self::assertEquals('nop', $conn->fetchColumn('SELECT id4 FROM vct_auxiliary LIMIT 1'));

        self::assertEquals('qrs', $conn->fetchColumn('SELECT id1 FROM vct_inversed_manytomany_compositeid_foreignkey LIMIT 1'));
        self::assertEquals('nop', $conn->fetchColumn('SELECT foreign_id FROM vct_inversed_manytomany_compositeid_foreignkey LIMIT 1'));

        self::assertEquals('tuv', $conn->fetchColumn('SELECT id2 FROM vct_owning_manytomany_compositeid_foreignkey LIMIT 1'));

        self::assertEquals('qrs', $conn->fetchColumn('SELECT associated_id FROM vct_xref_manytomany_compositeid_foreignkey LIMIT 1'));
        self::assertEquals('nop', $conn->fetchColumn('SELECT associated_foreign_id FROM vct_xref_manytomany_compositeid_foreignkey LIMIT 1'));
        self::assertEquals('tuv', $conn->fetchColumn('SELECT owning_id FROM vct_xref_manytomany_compositeid_foreignkey LIMIT 1'));
    }

    public function testThatEntitiesAreFetchedFromTheDatabase() : void
    {
        $auxiliary = $this->em->find(Entity\AuxiliaryEntity::class, 'abc');

        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdForeignKeyEntity::class,
            ['id1' => 'def', 'foreignEntity' => 'abc']
        );

        $owning = $this->em->find(Entity\OwningManyToManyCompositeIdForeignKeyEntity::class, 'ghi');

        self::assertInstanceOf(Entity\AuxiliaryEntity::class, $auxiliary);
        self::assertInstanceOf(Entity\InversedManyToManyCompositeIdForeignKeyEntity::class, $inversed);
        self::assertInstanceOf(Entity\OwningManyToManyCompositeIdForeignKeyEntity::class, $owning);
    }

    public function testThatTheValueOfIdentifiersAreConvertedBackAfterBeingFetchedFromTheDatabase() : void
    {
        $auxiliary = $this->em->find(Entity\AuxiliaryEntity::class, 'abc');

        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdForeignKeyEntity::class,
            ['id1' => 'def', 'foreignEntity' => 'abc']
        );

        $owning = $this->em->find(Entity\OwningManyToManyCompositeIdForeignKeyEntity::class, 'ghi');

        self::assertEquals('abc', $auxiliary->id4);
        self::assertEquals('def', $inversed->id1);
        self::assertEquals('abc', $inversed->foreignEntity->id4);
        self::assertEquals('ghi', $owning->id2);
    }

    public function testThatInversedEntityIsFetchedFromTheDatabaseUsingAuxiliaryEntityAsId() : void
    {
        $auxiliary = $this->em->find(Entity\AuxiliaryEntity::class, 'abc');

        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdForeignKeyEntity::class,
            ['id1' => 'def', 'foreignEntity' => $auxiliary]
        );

        self::assertInstanceOf(Entity\InversedManyToManyCompositeIdForeignKeyEntity::class, $inversed);
    }

    public function testThatTheCollectionFromOwningToInversedIsLoaded() : void
    {
        $owning = $this->em->find(Entity\OwningManyToManyCompositeIdForeignKeyEntity::class, 'ghi');

        self::assertCount(1, $owning->associatedEntities);
    }

    public function testThatTheCollectionFromInversedToOwningIsLoaded() : void
    {
        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdForeignKeyEntity::class,
            ['id1' => 'def', 'foreignEntity' => 'abc']
        );

        self::assertCount(1, $inversed->associatedEntities);
    }

    public function testThatTheJoinTableRowsAreRemovedWhenRemovingTheAssociation() : void
    {
        $conn = $this->em->getConnection();

        // remove association

        $inversed = $this->em->find(
            Entity\InversedManyToManyCompositeIdForeignKeyEntity::class,
            ['id1' => 'def', 'foreignEntity' => 'abc']
        );

        foreach ($inversed->associatedEntities as $owning) {
            $inversed->associatedEntities->removeElement($owning);
            $owning->associatedEntities->removeElement($inversed);
        }

        $this->em->flush();
        $this->em->clear();

        // test association is removed

        self::assertEquals(0, $conn->fetchColumn('SELECT COUNT(*) FROM vct_xref_manytomany_compositeid_foreignkey'));
    }
}
