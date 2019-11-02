<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\ORM\PersistentCollection;
use Doctrine\Tests\Models\ECommerce\ECommerceCart;
use Doctrine\Tests\Models\ECommerce\ECommerceCustomer;
use Doctrine\Tests\Models\ECommerce\ECommerceFeature;
use Doctrine\Tests\Models\ECommerce\ECommerceProduct;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * Tests capabilities of the persister.
 */
class StandardEntityPersisterTest extends OrmFunctionalTestCase
{
    protected function setUp() : void
    {
        $this->useModelSet('ecommerce');
        parent::setUp();
    }

    public function testAcceptsForeignKeysAsCriteria() : void
    {
        $customer = new ECommerceCustomer();
        $customer->setName('John Doe');
        $cart = new ECommerceCart();
        $cart->setPayment('Credit card');
        $customer->setCart($cart);
        $this->em->persist($customer);
        $this->em->flush();
        $this->em->clear();
        $cardId = $cart->getId();
        unset($cart);

        $class = $this->em->getClassMetadata(ECommerceCart::class);

        $persister = $this->em->getUnitOfWork()->getEntityPersister(ECommerceCart::class);
        $newCart   = new ECommerceCart();
        $this->em->getUnitOfWork()->registerManaged($newCart, ['id' => $cardId], []);
        $persister->load(['customer_id' => $customer->getId()], $newCart, $class->getProperty('customer'));
        self::assertEquals('Credit card', $newCart->getPayment());
    }

    /**
     * Ticket #2478 from Damon Jones (dljones)
     */
    public function testAddPersistRetrieve() : void
    {
        $f1 = new ECommerceFeature();
        $f1->setDescription('AC-3');

        $f2 = new ECommerceFeature();
        $f2->setDescription('DTS');

        $p = new ECommerceProduct();
        $p->addFeature($f1);
        $p->addFeature($f2);
        $this->em->persist($p);

        $this->em->flush();

        self::assertCount(2, $p->getFeatures());
        self::assertInstanceOf(PersistentCollection::class, $p->getFeatures());

        $q = $this->em->createQuery(
            'SELECT p, f
               FROM Doctrine\Tests\Models\ECommerce\ECommerceProduct p
               JOIN p.features f'
        );

        $res = $q->getResult();

        self::assertCount(2, $p->getFeatures());
        self::assertInstanceOf(PersistentCollection::class, $p->getFeatures());

        // Check that the features are the same instances still
        foreach ($p->getFeatures() as $feature) {
            if ($feature->getDescription() === 'AC-3') {
                self::assertSame($feature, $f1);
            } else {
                self::assertSame($feature, $f2);
            }
        }

        // Now we test how Hydrator affects IdentityMap
        // (change from ArrayCollection to PersistentCollection)
        $f3 = new ECommerceFeature();
        $f3->setDescription('XVID');
        $p->addFeature($f3);

        // Now we persist the Feature #3
        $this->em->persist($p);
        $this->em->flush();

        $q = $this->em->createQuery(
            'SELECT p, f
               FROM Doctrine\Tests\Models\ECommerce\ECommerceProduct p
               JOIN p.features f'
        );

        $res = $q->getResult();

        // Persisted Product now must have 3 Feature items
        self::assertCount(3, $res[0]->getFeatures());
    }
}
