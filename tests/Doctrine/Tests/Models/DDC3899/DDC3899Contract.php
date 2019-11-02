<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\DDC3899;

use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dc3899_contracts")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "fix"       = DDC3899FixContract::class,
 *     "flexible"  = DDC3899FlexContract::class
 * })
 */
abstract class DDC3899Contract
{
    /** @ORM\Id @ORM\Column(type="integer") */
    public $id;

    /** @ORM\Column(type="boolean") */
    public $completed = false;

    /** @ORM\ManyToOne(targetEntity=DDC3899User::class, inversedBy="contract") */
    public $user;
}
