<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\Models;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-1041
 */
class DDC1041Test extends OrmFunctionalTestCase
{
    public function setUp() : void
    {
        $this->useModelSet('company');
        parent::setUp();
    }

    public function testGrabWrongSubtypeReturnsNull() : void
    {
        $fix = new Models\Company\CompanyFixContract();
        $fix->setFixPrice(2000);

        $this->em->persist($fix);
        $this->em->flush();

        $id = $fix->getId();

        self::assertNull($this->em->find(Models\Company\CompanyFlexContract::class, $id));
        self::assertNull($this->em->getReference(Models\Company\CompanyFlexContract::class, $id));
        self::assertNull($this->em->getPartialReference(Models\Company\CompanyFlexContract::class, $id));
    }
}
