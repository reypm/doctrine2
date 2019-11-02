<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Annotation as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;

class DDC588Test extends OrmFunctionalTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->schemaTool->createSchema(
            [
                $this->em->getClassMetadata(DDC588Site::class),
            ]
        );
    }

    public function testIssue() : void
    {
        $site = new DDC588Site('Foo');

        $this->em->persist($site);
        $this->em->flush();

        // Following should not result in exception
        $this->em->refresh($site);

        $this->addToAssertionCount(1);
    }
}

/**
 * @ORM\Entity
 */
class DDC588Site
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="site_id")
     * @ORM\GeneratedValue
     */
    public $id;

    /** @ORM\Column(type="string", length=45) */
    protected $name;

    public function __construct($name = '')
    {
        $this->name = $name;
    }
}
