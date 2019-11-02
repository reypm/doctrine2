<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Annotation as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;

class Ticket4646InstanceOfAbstractTest extends OrmFunctionalTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->schemaTool->createSchema([
            $this->em->getClassMetadata(PersonTicket4646Abstract::class),
            $this->em->getClassMetadata(EmployeeTicket4646Abstract::class),
        ]);
    }

    public function testInstanceOf() : void
    {
        $this->em->persist(new EmployeeTicket4646Abstract());
        $this->em->flush();

        $dql    = 'SELECT p FROM Doctrine\Tests\ORM\Functional\Ticket\PersonTicket4646Abstract p
                WHERE p INSTANCE OF Doctrine\Tests\ORM\Functional\Ticket\PersonTicket4646Abstract';
        $query  = $this->em->createQuery($dql);
        $result = $query->getResult();

        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(PersonTicket4646Abstract::class, $result);
    }
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="instance_of_abstract_test_person")
 * @ORM\InheritanceType(value="JOINED")
 * @ORM\DiscriminatorColumn(name="kind", type="string")
 * @ORM\DiscriminatorMap(value={
 *     "employee": EmployeeTicket4646Abstract::class
 * })
 */
abstract class PersonTicket4646Abstract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId() : ?int
    {
        return $this->id;
    }
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="instance_of_abstract_test_employee")
 */
class EmployeeTicket4646Abstract extends PersonTicket4646Abstract
{
}
