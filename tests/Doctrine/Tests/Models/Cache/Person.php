<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\Cache;

use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("cache_person")
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /** @ORM\Column(unique=true) */
    public $name;

    /** @ORM\OneToOne(targetEntity=Address::class, mappedBy="person") */
    public $address;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
