<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\DDC3899;

use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dc3899_users")
 */
class DDC3899User
{
    /** @ORM\Id @ORM\Column(type="integer") */
    public $id;

    /** @ORM\OneToMany(targetEntity=DDC3899Contract::class, mappedBy="user") */
    public $contracts;
}
