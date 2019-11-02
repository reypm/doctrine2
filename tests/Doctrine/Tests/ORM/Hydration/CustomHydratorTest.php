<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Hydration;

use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

class CustomHydratorTest extends HydrationTestCase
{
    public function testCustomHydrator() : void
    {
        $em     = $this->getTestEntityManager();
        $config = $em->getConfiguration();
        $config->addCustomHydrationMode('CustomHydrator', CustomHydrator::class);

        $hydrator = $em->newHydrator('CustomHydrator');
        self::assertInstanceOf(CustomHydrator::class, $hydrator);
        self::assertNull($config->getCustomHydrationMode('does not exist'));
    }
}

class CustomHydrator extends AbstractHydrator
{
    protected function hydrateAllData()
    {
        return $this->stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
