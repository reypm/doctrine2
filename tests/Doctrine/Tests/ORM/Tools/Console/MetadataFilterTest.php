<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Tools\Console;

use Doctrine\ORM\Annotation as ORM;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\Tests\OrmTestCase;
use function count;

/**
 * Tests for {@see \Doctrine\ORM\Tools\Console\MetadataFilter}
 *
 * @covers \Doctrine\ORM\Tools\Console\MetadataFilter
 */
class MetadataFilterTest extends OrmTestCase
{
    /** @var DisconnectedClassMetadataFactory */
    private $cmf;

    protected function setUp() : void
    {
        parent::setUp();

        $driver = $this->createAnnotationDriver();
        $em     = $this->getTestEntityManager();

        $em->getConfiguration()->setMetadataDriverImpl($driver);

        $this->cmf = new DisconnectedClassMetadataFactory();
        $this->cmf->setEntityManager($em);
    }

    public function testFilterWithEmptyArray() : void
    {
        $originalMetadatas = [
            $metadataAaa = $this->cmf->getMetadataFor(MetadataFilterTestEntityAaa::class),
            $metadataBbb = $this->cmf->getMetadataFor(MetadataFilterTestEntityBbb::class),
        ];

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, []);

        self::assertContains($metadataAaa, $metadatas);
        self::assertContains($metadataBbb, $metadatas);
        self::assertCount(count($originalMetadatas), $metadatas);
    }

    public function testFilterWithString() : void
    {
        $originalMetadatas = [
            $metadataAaa = $this->cmf->getMetadataFor(MetadataFilterTestEntityAaa::class),
            $metadataBbb = $this->cmf->getMetadataFor(MetadataFilterTestEntityBbb::class),
            $metadataCcc = $this->cmf->getMetadataFor(MetadataFilterTestEntityCcc::class),
        ];

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, 'MetadataFilterTestEntityAaa');

        self::assertContains($metadataAaa, $metadatas);
        self::assertNotContains($metadataBbb, $metadatas);
        self::assertNotContains($metadataCcc, $metadatas);
        self::assertCount(1, $metadatas);

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, 'MetadataFilterTestEntityBbb');

        self::assertNotContains($metadataAaa, $metadatas);
        self::assertContains($metadataBbb, $metadatas);
        self::assertNotContains($metadataCcc, $metadatas);
        self::assertCount(1, $metadatas);

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, 'MetadataFilterTestEntityCcc');

        self::assertNotContains($metadataAaa, $metadatas);
        self::assertNotContains($metadataBbb, $metadatas);
        self::assertContains($metadataCcc, $metadatas);
        self::assertCount(1, $metadatas);
    }

    public function testFilterWithString2() : void
    {
        $originalMetadatas = [
            $metadataFoo    = $this->cmf->getMetadataFor(MetadataFilterTestEntityFoo::class),
            $metadataFooBar = $this->cmf->getMetadataFor(MetadataFilterTestEntityFooBar::class),
            $metadataBar    = $this->cmf->getMetadataFor(MetadataFilterTestEntityBar::class),
        ];

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, 'MetadataFilterTestEntityFoo');

        self::assertContains($metadataFoo, $metadatas);
        self::assertContains($metadataFooBar, $metadatas);
        self::assertNotContains($metadataBar, $metadatas);
        self::assertCount(2, $metadatas);
    }

    public function testFilterWithArray() : void
    {
        $originalMetadatas = [
            $metadataAaa = $this->cmf->getMetadataFor(MetadataFilterTestEntityAaa::class),
            $metadataBbb = $this->cmf->getMetadataFor(MetadataFilterTestEntityBbb::class),
            $metadataCcc = $this->cmf->getMetadataFor(MetadataFilterTestEntityCcc::class),
        ];

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, [
            'MetadataFilterTestEntityAaa',
            'MetadataFilterTestEntityCcc',
        ]);

        self::assertContains($metadataAaa, $metadatas);
        self::assertNotContains($metadataBbb, $metadatas);
        self::assertContains($metadataCcc, $metadatas);
        self::assertCount(2, $metadatas);
    }

    public function testFilterWithRegex() : void
    {
        $originalMetadatas = [
            $metadataFoo    = $this->cmf->getMetadataFor(MetadataFilterTestEntityFoo::class),
            $metadataFooBar = $this->cmf->getMetadataFor(MetadataFilterTestEntityFooBar::class),
            $metadataBar    = $this->cmf->getMetadataFor(MetadataFilterTestEntityBar::class),
        ];

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, 'Foo$');

        self::assertContains($metadataFoo, $metadatas);
        self::assertNotContains($metadataFooBar, $metadatas);
        self::assertNotContains($metadataBar, $metadatas);
        self::assertCount(1, $metadatas);

        $metadatas = $originalMetadatas;
        $metadatas = MetadataFilter::filter($metadatas, 'Bar$');

        self::assertNotContains($metadataFoo, $metadatas);
        self::assertContains($metadataFooBar, $metadatas);
        self::assertContains($metadataBar, $metadatas);
        self::assertCount(2, $metadatas);
    }
}

/** @ORM\Entity */
class MetadataFilterTestEntityAaa
{
    /** @ORM\Id @ORM\Column */
    protected $id;
}

/** @ORM\Entity */
class MetadataFilterTestEntityBbb
{
    /** @ORM\Id @ORM\Column */
    protected $id;
}

/** @ORM\Entity */
class MetadataFilterTestEntityCcc
{
    /** @ORM\Id @ORM\Column */
    protected $id;
}

/** @ORM\Entity */
class MetadataFilterTestEntityFoo
{
    /** @ORM\Id @ORM\Column */
    protected $id;
}

/** @ORM\Entity */
class MetadataFilterTestEntityBar
{
    /** @ORM\Id @ORM\Column */
    protected $id;
}

/** @ORM\Entity */
class MetadataFilterTestEntityFooBar
{
    /** @ORM\Id @ORM\Column */
    protected $id;
}
