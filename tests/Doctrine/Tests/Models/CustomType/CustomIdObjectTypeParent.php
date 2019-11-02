<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\CustomType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Annotation as ORM;
use Doctrine\Tests\DbalTypes\CustomIdObject;

/**
 * @ORM\Entity
 * @ORM\Table(name="custom_id_type_parent")
 */
class CustomIdObjectTypeParent
{
    /**
     * @ORM\Id @ORM\Column(type="CustomIdObject")
     *
     * @var CustomIdObject
     */
    public $id;

    /** @ORM\OneToMany(targetEntity=CustomIdObjectTypeChild::class, cascade={"persist", "remove"}, mappedBy="parent") */
    public $children;

    public function __construct(CustomIdObject $id)
    {
        $this->id       = $id;
        $this->children = new ArrayCollection();
    }
}
