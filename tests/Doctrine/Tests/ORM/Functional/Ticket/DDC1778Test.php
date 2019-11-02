<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\Models\CMS\CmsPhonenumber;
use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-1778
 */
class DDC1778Test extends OrmFunctionalTestCase
{
    private $user;
    private $phone;

    public function setUp() : void
    {
        $this->useModelSet('cms');
        parent::setUp();

        $this->user           = new CmsUser();
        $this->user->username = 'beberlei';
        $this->user->name     = 'Benjamin';
        $this->user->status   = 'active';

        $this->phone              = new CmsPhonenumber();
        $this->phone->phonenumber = '0123456789';
        $this->user->addPhonenumber($this->phone);

        $this->em->persist($this->user);
        $this->em->persist($this->phone);
        $this->em->flush();
        $this->em->clear();

        $this->user  = $this->em->find(CmsUser::class, $this->user->getId());
        $this->phone = $this->em->find(CmsPhonenumber::class, $this->phone->phonenumber);
    }

    public function testClear() : void
    {
        $clonedNumbers = clone $this->user->getPhonenumbers();
        $clonedNumbers->clear();
        $this->em->flush();
        $this->em->clear();

        $this->user = $this->em->find(CmsUser::class, $this->user->getId());

        self::assertCount(1, $this->user->getPhonenumbers());
    }

    public function testRemove() : void
    {
        $clonedNumbers = clone $this->user->getPhonenumbers();
        $clonedNumbers->remove(0);
        $this->em->flush();
        $this->em->clear();

        $this->user = $this->em->find(CmsUser::class, $this->user->getId());

        self::assertCount(1, $this->user->getPhonenumbers());
    }

    public function testRemoveElement() : void
    {
        $clonedNumbers = clone $this->user->getPhonenumbers();
        $clonedNumbers->removeElement($this->phone);
        $this->em->flush();
        $this->em->clear();

        $this->user = $this->em->find(CmsUser::class, $this->user->getId());

        self::assertCount(1, $this->user->getPhonenumbers());
    }
}
