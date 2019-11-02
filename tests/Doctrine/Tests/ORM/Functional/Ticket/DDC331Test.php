<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * Functional tests for the Class Table Inheritance mapping strategy.
 */
class DDC331Test extends OrmFunctionalTestCase
{
    protected function setUp() : void
    {
        $this->useModelSet('company');
        parent::setUp();
    }

    /**
     * @group DDC-331
     */
    public function testSelectFieldOnRootEntity() : void
    {
        $q = $this->em->createQuery('SELECT e.name FROM Doctrine\Tests\Models\Company\CompanyEmployee e');

        self::assertSQLEquals(
            'SELECT t0."name" AS c0 FROM "company_employees" t1 INNER JOIN "company_persons" t0 ON t1."id" = t0."id" LEFT JOIN "company_managers" t2 ON t1."id" = t2."id"',
            $q->getSQL()
        );
    }
}
