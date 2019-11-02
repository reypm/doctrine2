<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Annotation as ORM;
use Doctrine\ORM\Query;
use Doctrine\Tests\OrmFunctionalTestCase;
use function sprintf;

/**
 * @group DDC-2224
 */
class DDC2224Test extends OrmFunctionalTestCase
{
    public static function setUpBeforeClass()
    {
        Type::addType('DDC2224Type', DDC2224Type::class);
    }

    public function testIssue() : Query
    {
        $dql   = 'SELECT e FROM ' . __NAMESPACE__ . '\DDC2224Entity e WHERE e.field = :field';
        $query = $this->em->createQuery($dql);
        $query->setQueryCacheDriver(new ArrayCache());

        $query->setParameter('field', 'test', 'DDC2224Type');

        self::assertStringEndsWith('."field" = FUNCTION(?)', $query->getSQL());

        return $query;
    }

    /**
     * @depends testIssue
     */
    public function testCacheMissWhenTypeChanges(Query $query) : void
    {
        $query->setParameter('field', 'test', 'string');

        self::assertStringEndsWith('."field" = ?', $query->getSQL());
    }
}

class DDC2224Type extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName()
    {
        return 'DDC2224Type';
    }

    /**
     * {@inheritdoc}
     */
    public function canRequireSQLConversion()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return sprintf('FUNCTION(%s)', $sqlExpr);
    }
}

/**
 * @ORM\Entity
 */
class DDC2224Entity
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    public $id;

    /** @ORM\Column(type="DDC2224Type") */
    public $field;
}
