<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\Annotation as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;
use ProxyManager\Proxy\GhostObjectInterface;

/**
 * @group embedded
 */
class DDC6460Test extends OrmFunctionalTestCase
{
    public function setUp() : void
    {
        parent::setUp();

        try {
            $this->setUpEntitySchema(
                [
                    DDC6460Entity::class,
                    DDC6460ParentEntity::class,
                ]
            );
        } catch (SchemaException $e) {
        }
    }

    /**
     * @group DDC-6460
     */
    public function testInlineEmbeddable() : void
    {
        $isFieldMapped = $this->em
            ->getClassMetadata(DDC6460Entity::class)
            ->hasField('embedded');

        self::assertTrue($isFieldMapped);
    }

    /**
     * @group DDC-6460
     */
    public function testInlineEmbeddableProxyInitialization() : void
    {
        $entity                  = new DDC6460Entity();
        $entity->id              = 1;
        $entity->embedded        = new DDC6460Embeddable();
        $entity->embedded->field = 'test';

        $this->em->persist($entity);

        $second             = new DDC6460ParentEntity();
        $second->id         = 1;
        $second->lazyLoaded = $entity;

        $this->em->persist($second);
        $this->em->flush();
        $this->em->clear();

        $secondEntityWithLazyParameter = $this->em->getRepository(DDC6460ParentEntity::class)->findOneById(1);

        self::assertInstanceOf(GhostObjectInterface::class, $secondEntityWithLazyParameter->lazyLoaded);
        self::assertInstanceOf(DDC6460Entity::class, $secondEntityWithLazyParameter->lazyLoaded);
        self::assertFalse($secondEntityWithLazyParameter->lazyLoaded->isProxyInitialized());
        self::assertEquals($secondEntityWithLazyParameter->lazyLoaded->embedded, $entity->embedded);
        self::assertTrue($secondEntityWithLazyParameter->lazyLoaded->isProxyInitialized());
    }
}

/**
 * @ORM\Embeddable()
 */
class DDC6460Embeddable
{
    /** @ORM\Column(type="string") */
    public $field;
}

/**
 * @ORM\Entity()
 */
class DDC6460Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "NONE")
     * @ORM\Column(type = "integer")
     */
    public $id;

    /** @ORM\Embedded(class = "DDC6460Embeddable") */
    public $embedded;
}

/**
 * @ORM\Entity()
 */
class DDC6460ParentEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "NONE")
     * @ORM\Column(type = "integer")
     */
    public $id;

    /** @ORM\ManyToOne(targetEntity = DDC6460Entity::class, fetch="EXTRA_LAZY", cascade={"persist"}) */
    public $lazyLoaded;
}
