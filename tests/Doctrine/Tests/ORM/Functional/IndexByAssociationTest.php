<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\Models\StockExchange\Bond;
use Doctrine\Tests\Models\StockExchange\Market;
use Doctrine\Tests\Models\StockExchange\Stock;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-250
 */
class IndexByAssociationTest extends OrmFunctionalTestCase
{
    /** @var Market */
    private $market;

    private $bond;

    public function setUp() : void
    {
        $this->useModelSet('stockexchange');
        parent::setUp();
        $this->loadFixture();
    }

    public function loadFixture()
    {
        $this->market = new Market('Some Exchange');
        $stock1       = new Stock('AAPL', 10, $this->market);
        $stock2       = new Stock('GOOG', 20, $this->market);

        $this->bond = new Bond('MyBond');
        $this->bond->addStock($stock1);
        $this->bond->addStock($stock2);

        $this->em->persist($this->market);
        $this->em->persist($stock1);
        $this->em->persist($stock2);
        $this->em->persist($this->bond);
        $this->em->flush();
        $this->em->clear();
    }

    public function testManyToOneFinder() : void
    {
        /** @var Market $market */
        $market = $this->em->find(Market::class, $this->market->getId());

        self::assertCount(2, $market->stocks);
        self::assertTrue(isset($market->stocks['AAPL']), 'AAPL symbol has to be key in indexed association.');
        self::assertTrue(isset($market->stocks['GOOG']), 'GOOG symbol has to be key in indexed association.');
        self::assertEquals('AAPL', $market->stocks['AAPL']->getSymbol());
        self::assertEquals('GOOG', $market->stocks['GOOG']->getSymbol());
    }

    public function testManyToOneDQL() : void
    {
        $dql    = 'SELECT m, s FROM Doctrine\Tests\Models\StockExchange\Market m JOIN m.stocks s WHERE m.id = ?1';
        $market = $this->em->createQuery($dql)->setParameter(1, $this->market->getId())->getSingleResult();

        self::assertCount(2, $market->stocks);
        self::assertTrue(isset($market->stocks['AAPL']), 'AAPL symbol has to be key in indexed association.');
        self::assertTrue(isset($market->stocks['GOOG']), 'GOOG symbol has to be key in indexed association.');
        self::assertEquals('AAPL', $market->stocks['AAPL']->getSymbol());
        self::assertEquals('GOOG', $market->stocks['GOOG']->getSymbol());
    }

    public function testManyToMany() : void
    {
        $bond = $this->em->find(Bond::class, $this->bond->getId());

        self::assertCount(2, $bond->stocks);
        self::assertTrue(isset($bond->stocks['AAPL']), 'AAPL symbol has to be key in indexed association.');
        self::assertTrue(isset($bond->stocks['GOOG']), 'GOOG symbol has to be key in indexed association.');
        self::assertEquals('AAPL', $bond->stocks['AAPL']->getSymbol());
        self::assertEquals('GOOG', $bond->stocks['GOOG']->getSymbol());
    }

    public function testManytoManyDQL() : void
    {
        $dql  = 'SELECT b, s FROM Doctrine\Tests\Models\StockExchange\Bond b JOIN b.stocks s WHERE b.id = ?1';
        $bond = $this->em->createQuery($dql)->setParameter(1, $this->bond->getId())->getSingleResult();

        self::assertCount(2, $bond->stocks);
        self::assertTrue(isset($bond->stocks['AAPL']), 'AAPL symbol has to be key in indexed association.');
        self::assertTrue(isset($bond->stocks['GOOG']), 'GOOG symbol has to be key in indexed association.');
        self::assertEquals('AAPL', $bond->stocks['AAPL']->getSymbol());
        self::assertEquals('GOOG', $bond->stocks['GOOG']->getSymbol());
    }

    public function testDqlOverrideIndexBy() : void
    {
        $dql  = 'SELECT b, s FROM Doctrine\Tests\Models\StockExchange\Bond b JOIN b.stocks s INDEX BY s.id WHERE b.id = ?1';
        $bond = $this->em->createQuery($dql)->setParameter(1, $this->bond->getId())->getSingleResult();

        self::assertCount(2, $bond->stocks);
        self::assertFalse(isset($bond->stocks['AAPL']), 'AAPL symbol not exists in re-indexed association.');
        self::assertFalse(isset($bond->stocks['GOOG']), 'GOOG symbol not exists in re-indexed association.');
    }
}
