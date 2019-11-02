<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\DDC1872;

use Doctrine\ORM\Annotation as ORM;

/**
 * Trait class
 */
trait DDC1872Example
{
    /** @ORM\Id @ORM\Column(type="string") */
    private $id;

    /** @ORM\Column(name="trait_foo", type="integer", length=100, nullable=true, unique=true) */
    protected $foo;

    /**
     * @ORM\OneToOne(targetEntity=DDC1872Bar::class, cascade={"persist"})
     * @ORM\JoinColumn(name="example_trait_bar_id", referencedColumnName="id")
     */
    protected $bar;
}
