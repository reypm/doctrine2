<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\CMS;

use Doctrine\ORM\Annotation as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_phonenumbers")
 */
class CmsPhonenumber
{
    /** @ORM\Id @ORM\Column(length=50) */
    public $phonenumber;
    /**
     * @ORM\ManyToOne(targetEntity=CmsUser::class, inversedBy="phonenumbers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    public $user;

    public function setUser(CmsUser $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
