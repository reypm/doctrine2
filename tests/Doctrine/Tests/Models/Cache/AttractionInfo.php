<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\Cache;

use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Cache
 * @ORM\Entity
 * @ORM\Table("cache_attraction_info")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorMap({
 *  1  = AttractionContactInfo::class,
 *  2  = AttractionLocationInfo::class,
 * })
 */
abstract class AttractionInfo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Cache
     * @ORM\ManyToOne(targetEntity=Attraction::class, inversedBy="infos")
     * @ORM\JoinColumn(name="attraction_id", referencedColumnName="id")
     */
    protected $attraction;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getAttraction()
    {
        return $this->attraction;
    }

    public function setAttraction(Attraction $attraction)
    {
        $this->attraction = $attraction;

        $attraction->addInfo($this);
    }
}
