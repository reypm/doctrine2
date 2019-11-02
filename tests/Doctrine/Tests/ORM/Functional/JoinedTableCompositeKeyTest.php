<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\Models\CompositeKeyInheritance\JoinedChildClass;
use Doctrine\Tests\Models\CompositeKeyInheritance\JoinedRootClass;
use Doctrine\Tests\OrmFunctionalTestCase;

class JoinedTableCompositeKeyTest extends OrmFunctionalTestCase
{
    public function setUp() : void
    {
        $this->useModelSet('compositekeyinheritance');
        parent::setUp();
    }

    public function testInsertWithCompositeKey() : void
    {
        $childEntity = new JoinedChildClass();
        $this->em->persist($childEntity);
        $this->em->flush();

        $this->em->clear();

        $entity = $this->findEntity();
        self::assertEquals($childEntity, $entity);
    }

    /**
     * @group non-cacheable
     */
    public function testUpdateWithCompositeKey() : void
    {
        $childEntity = new JoinedChildClass();
        $this->em->persist($childEntity);
        $this->em->flush();

        $this->em->clear();

        $entity            = $this->findEntity();
        $entity->extension = 'ext-new';
        $this->em->persist($entity);
        $this->em->flush();

        $this->em->clear();

        $persistedEntity = $this->findEntity();
        self::assertEquals($entity, $persistedEntity);
    }

    /**
     * @return JoinedChildClass
     */
    private function findEntity()
    {
        return $this->em->find(
            JoinedRootClass::class,
            ['keyPart1' => 'part-1', 'keyPart2' => 'part-2']
        );
    }
}
