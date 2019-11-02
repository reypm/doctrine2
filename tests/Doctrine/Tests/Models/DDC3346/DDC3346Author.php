<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\DDC3346;

use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ddc3346_users")
 */
class DDC3346Author
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    public $id;

    /** @ORM\Column(type="string", length=255, unique=true) */
    public $username;

    /** @ORM\OneToMany(targetEntity=DDC3346Article::class, mappedBy="user", fetch="EAGER") */
    public $articles = [];
}
