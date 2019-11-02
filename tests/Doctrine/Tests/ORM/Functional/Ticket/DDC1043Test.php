<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-1043
 */
class DDC1043Test extends OrmFunctionalTestCase
{
    public function setUp() : void
    {
        $this->useModelSet('cms');
        parent::setUp();
    }

    public function testChangeSetPlusWeirdPHPCastingIntCastingRule() : void
    {
        $user           = new CmsUser();
        $user->name     = 'John Galt';
        $user->username = 'jgalt';
        $user->status   = '+44';

        $this->em->persist($user);
        $this->em->flush();

        $user->status = '44';
        $this->em->flush();
        $this->em->clear();

        $user = $this->em->find(CmsUser::class, $user->id);
        self::assertSame('44', $user->status);
    }
}
