<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\Taxi;

use Doctrine\ORM\Annotation as ORM;

/**
 * Test model that contains only Id-columns
 *
 * @ORM\Entity
 * @ORM\Table(name="taxi_ride")
 */
class Ride
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Driver::class, inversedBy="freeDriverRides")
     * @ORM\JoinColumn(name="driver_id", referencedColumnName="id")
     */
    private $driver;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Car::class, inversedBy="freeCarRides")
     * @ORM\JoinColumn(name="car", referencedColumnName="brand")
     */
    private $car;

    public function __construct(Driver $driver, Car $car)
    {
        $this->driver = $driver;
        $this->car    = $car;
    }
}
