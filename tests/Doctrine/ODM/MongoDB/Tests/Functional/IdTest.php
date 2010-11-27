<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional;

class IdTest extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testUuidId()
    {
        $user = new UuidUser('Jonathan H. Wage');
        $this->dm->persist($user);
        $this->dm->flush();
        $id = $user->id;

        $this->dm->clear();
        $check1 = $this->dm->findOne(__NAMESPACE__.'\UuidUser', array('id' => $id));
        $this->assertNotNull($check1);

        $check2 = $this->dm->createQueryBuilder(__NAMESPACE__.'\UuidUser')
            ->field('id')->equals($id)->getQuery()->getSingleResult();
        $this->assertNotNull($check2);
        $this->assertSame($check1, $check2);

        $check3 = $this->dm->createQueryBuilder(__NAMESPACE__.'\UuidUser')
            ->field('name')->equals('Jonathan H. Wage')->getQuery()->getSingleResult();
        $this->assertNotNull($check3);
        $this->assertSame($check2, $check3);
    }

    public function testCollectionId()
    {
        $user1 = new CollectionIdUser('Jonathan H. Wage');
        $reference1 = new ReferencedCollectionId('referenced 1');
        $user1->reference = $reference1;

        $user2 = new CollectionIdUser('Jonathan H. Wage');

        $reference2 = new ReferencedCollectionId('referenced 2');
        $user2->reference = $reference2;

        $this->dm->persist($user1);
        $this->dm->persist($user2);
        $this->dm->flush();
        $this->dm->clear();

        $this->assertEquals($user1->id, 1);
        $this->assertEquals($user2->id, 2);

        $this->assertEquals($reference1->id, 1);
        $this->assertEquals($reference2->id, 2);

        $check1 = $this->dm->findOne(__NAMESPACE__.'\CollectionIdUser', array('id' => $user1->id));
        $check2 = $this->dm->findOne(__NAMESPACE__.'\CollectionIdUser', array('id' => $user2->id));
        $this->assertNotNull($check1);
        $this->assertNotNull($check2);

        $this->assertEquals('referenced 1', $check1->reference->getName());
        $this->assertEquals('referenced 2', $check2->reference->getName());
    }

    public function testEmbeddedDocumentWithId()
    {
        $user1 = new CollectionIdUser('Jonathan H. Wage');
        $user1->embedded[] = new EmbeddedCollectionId('embedded #1');
        $user1->embedded[] = new EmbeddedCollectionId('embedded #2');
        $this->dm->persist($user1);
        $this->dm->flush();

        $user2 = new CollectionIdUser('Jonathan H. Wage');
        $user2->embedded[] = new EmbeddedCollectionId('embedded #1');
        $user2->embedded[] = new EmbeddedCollectionId('embedded #2');
        $this->dm->persist($user2);
        $this->dm->flush();

        $this->assertEquals($user1->id, 1);
        $this->assertEquals($user2->id, 2);

        $this->assertEquals($user1->embedded[0]->id, 1);
        $this->assertEquals($user1->embedded[1]->id, 2);

        $this->assertEquals($user2->embedded[0]->id, 3);
        $this->assertEquals($user2->embedded[1]->id, 4);
    }
}

/** @Document */
class UuidUser
{
    /** @Id(strategy="uuid") */
    public $id;

    /** @String(name="t") */
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}

/** @Document */
class CollectionIdUser
{
    /** @Id(strategy="increment") */
    public $id;

    /** @String(name="t") */
    public $name;

    /** @ReferenceOne(targetDocument="ReferencedCollectionId", cascade={"persist"}) */
    public $reference;

    /** @EmbedMany(targetDocument="EmbeddedCollectionId") */
    public $embedded = array();

    public function __construct($name)
    {
        $this->name = $name;
    }
}

/** @Document */
class ReferencedCollectionId
{
    /** @Id(strategy="increment") */
    public $id;

    /** @String */
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

/** @EmbeddedDocument */
class EmbeddedCollectionId
{
    /** @Id(strategy="increment") */
    public $id;

    /** @String */
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}