<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\Pagination;

use Doctrine\ORM\Annotation as ORM;

/**
 * Logo
 *
 * @ORM\Entity
 * @ORM\Table(name="pagination_logo")
 */
class Logo
{
    /**
     * @ORM\Column(type="integer") @ORM\Id
     * @ORM\GeneratedValue
     */
    public $id;

    /** @ORM\Column(type="string") */
    public $image;

    /** @ORM\Column(type="integer") */
    public $image_height;

    /** @ORM\Column(type="integer") */
    public $image_width;

    /**
     * @ORM\OneToOne(targetEntity=Company::class, inversedBy="logo", cascade={"persist"})
     * @ORM\JoinColumn(name="company_id")
     */
    public $company;
}
