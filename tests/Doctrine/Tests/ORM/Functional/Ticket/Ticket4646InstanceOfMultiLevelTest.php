<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Annotation as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;

class Ticket4646InstanceOfMultiLevelTest extends OrmFunctionalTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->schemaTool->createSchema([
            $this->em->getClassMetadata(PersonTicket4646MultiLevel::class),
            $this->em->getClassMetadata(EmployeeTicket4646MultiLevel::class),
            $this->em->getClassMetadata(EngineerTicket4646MultiLevel::class),
        ]);
    }

    public function testInstanceOf() : void
    {
        $this->em->persist(new PersonTicket4646MultiLevel());
        $this->em->persist(new EmployeeTicket4646MultiLevel());
        $this->em->persist(new EngineerTicket4646MultiLevel());
        $this->em->flush();

        $dql    = 'SELECT p FROM Doctrine\Tests\ORM\Functional\Ticket\PersonTicket4646MultiLevel p
                WHERE p INSTANCE OF Doctrine\Tests\ORM\Functional\Ticket\PersonTicket4646MultiLevel';
        $query  = $this->em->createQuery($dql);
        $result = $query->getResult();

        self::assertCount(3, $result);
        self::assertContainsOnlyInstancesOf(PersonTicket4646MultiLevel::class, $result);
    }
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="instance_of_multi_level_test_person")
 * @ORM\InheritanceType(value="JOINED")
 * @ORM\DiscriminatorColumn(name="kind", type="string")
 * @ORM\DiscriminatorMap(value={
 *     "person": "Doctrine\Tests\ORM\Functional\Ticket\PersonTicket4646MultiLevel",
 *     "employee": "Doctrine\Tests\ORM\Functional\Ticket\EmployeeTicket4646MultiLevel",
 *     "engineer": "Doctrine\Tests\ORM\Functional\Ticket\EngineerTicket4646MultiLevel",
 * })
 */
class PersonTicket4646MultiLevel
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
 * @ORM\Table(name="instance_of_multi_level_employee")
 */
class EmployeeTicket4646MultiLevel extends PersonTicket4646MultiLevel
{
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="instance_of_multi_level_engineer")
 */
class EngineerTicket4646MultiLevel extends EmployeeTicket4646MultiLevel
{
}
