<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\ValueConversionType;

use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="vct_inversed_onetoone_compositeid_foreignkey")
 */
class InversedOneToOneCompositeIdForeignKeyEntity
{
    /**
     * @ORM\Column(type="rot13")
     * @ORM\Id
     */
    public $id1;

    /**
     * @ORM\ManyToOne(targetEntity=AuxiliaryEntity::class)
     * @ORM\JoinColumn(name="foreign_id", referencedColumnName="id4")
     * @ORM\Id
     */
    public $foreignEntity;

    /** @ORM\Column(type="string", name="some_property") */
    public $someProperty;

    /** @ORM\OneToOne(targetEntity=OwningOneToOneCompositeIdForeignKeyEntity::class, mappedBy="associatedEntity") */
    public $associatedEntity;
}
