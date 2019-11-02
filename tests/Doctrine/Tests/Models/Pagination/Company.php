<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\Pagination;

use Doctrine\ORM\Annotation as ORM;

/**
 * Company
 *
 * @ORM\Entity
 * @ORM\Table(name="pagination_company")
 */
class Company
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    public $id;

    /** @ORM\Column(type="string") */
    public $name;

    /** @ORM\Column(type="string", name="jurisdiction_code", nullable=true) */
    public $jurisdiction;

    /**
     * @ORM\OneToOne(
     *     targetEntity=Logo::class,
     *     mappedBy="company",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    public $logo;

    /**
     * @ORM\OneToMany(
     *     targetEntity=Department::class,
     *     mappedBy="company",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    public $departments;
}
