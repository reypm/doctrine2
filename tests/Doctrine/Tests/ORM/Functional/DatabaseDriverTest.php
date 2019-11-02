<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\Mapping\ManyToOneAssociationMetadata;

class DatabaseDriverTest extends DatabaseDriverTestCase
{
    /** @var AbstractSchemaManager */
    protected $sm;

    public function setUp() : void
    {
        $this->useModelSet('cms');

        parent::setUp();

        $this->sm = $this->em->getConnection()->getSchemaManager();
    }

    /**
     * @group DDC-2059
     */
    public function testIssue2059() : void
    {
        if (! $this->em->getConnection()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $this->markTestSkipped('Platform does not support foreign keys.');
        }

        $user = new Table('ddc2059_user');
        $user->addColumn('id', 'integer');
        $user->setPrimaryKey(['id']);
        $project = new Table('ddc2059_project');
        $project->addColumn('id', 'integer');
        $project->addColumn('user_id', 'integer');
        $project->addColumn('user', 'string');
        $project->setPrimaryKey(['id']);
        $project->addForeignKeyConstraint('ddc2059_user', ['user_id'], ['id']);

        $metadata = $this->convertToClassMetadata([$project, $user], []);

        self::assertNotNull($metadata['Ddc2059Project']->getProperty('user'));
        self::assertNotNull($metadata['Ddc2059Project']->getProperty('user2'));
    }

    public function testLoadMetadataFromDatabase() : void
    {
        if (! $this->em->getConnection()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $this->markTestSkipped('Platform does not support foreign keys.');
        }

        $table = new Table('dbdriver_foo');
        $table->addColumn('id', 'integer');
        $table->setPrimaryKey(['id']);
        $table->addColumn('bar', 'string', ['notnull' => false, 'length' => 200]);

        $this->sm->dropAndCreateTable($table);

        $metadatas = $this->extractClassMetadata(['DbdriverFoo']);

        self::assertArrayHasKey('DbdriverFoo', $metadatas);

        $metadata = $metadatas['DbdriverFoo'];

        self::assertNotNull($metadata->getProperty('id'));

        $idProperty = $metadata->getProperty('id');

        self::assertEquals('id', $idProperty->getName());
        self::assertEquals('id', $idProperty->getColumnName());
        self::assertEquals('integer', $idProperty->getTypeName());

        self::assertNotNull($metadata->getProperty('bar'));

        $barProperty = $metadata->getProperty('bar');

        self::assertEquals('bar', $barProperty->getName());
        self::assertEquals('bar', $barProperty->getColumnName());
        self::assertEquals('string', $barProperty->getTypeName());
        self::assertEquals(200, $barProperty->getLength());
        self::assertTrue($barProperty->isNullable());
    }

    public function testLoadMetadataWithForeignKeyFromDatabase() : void
    {
        if (! $this->em->getConnection()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $this->markTestSkipped('Platform does not support foreign keys.');
        }

        $tableB = new Table('dbdriver_bar');
        $tableB->addColumn('id', 'integer');
        $tableB->setPrimaryKey(['id']);

        $this->sm->dropAndCreateTable($tableB);

        $tableA = new Table('dbdriver_baz');
        $tableA->addColumn('id', 'integer');
        $tableA->setPrimaryKey(['id']);
        $tableA->addColumn('bar_id', 'integer');
        $tableA->addForeignKeyConstraint('dbdriver_bar', ['bar_id'], ['id']);

        $this->sm->dropAndCreateTable($tableA);

        $metadatas = $this->extractClassMetadata(['DbdriverBar', 'DbdriverBaz']);

        self::assertArrayHasKey('DbdriverBaz', $metadatas);

        $bazMetadata = $metadatas['DbdriverBaz'];

        self::assertNull($bazMetadata->getProperty('barId'), "The foreign Key field should not be inflected, as 'barId' field is an association.");
        self::assertNotNull($bazMetadata->getProperty('id'));

        self::assertArrayHasKey('bar', $bazMetadata->getPropertiesIterator());
        self::assertInstanceOf(ManyToOneAssociationMetadata::class, $bazMetadata->getProperty('bar'));
    }

    public function testDetectManyToManyTables() : void
    {
        if (! $this->em->getConnection()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $this->markTestSkipped('Platform does not support foreign keys.');
        }

        $metadatas = $this->extractClassMetadata(['CmsUsers', 'CmsGroups', 'CmsTags']);

        self::assertArrayHasKey('CmsUsers', $metadatas, 'CmsUsers entity was not detected.');
        self::assertArrayHasKey('CmsGroups', $metadatas, 'CmsGroups entity was not detected.');
        self::assertArrayHasKey('CmsTags', $metadatas, 'CmsTags entity was not detected.');

        self::assertCount(3, $metadatas['CmsUsers']->getProperties());
        self::assertArrayHasKey('group', $metadatas['CmsUsers']->getProperties());
        self::assertCount(1, $metadatas['CmsGroups']->getProperties());
        self::assertArrayHasKey('user', $metadatas['CmsGroups']->getProperties());
        self::assertCount(1, $metadatas['CmsTags']->getProperties());
        self::assertArrayHasKey('user', $metadatas['CmsGroups']->getProperties());
    }

    public function testIgnoreManyToManyTableWithoutFurtherForeignKeyDetails() : void
    {
        $tableB = new Table('dbdriver_bar');
        $tableB->addColumn('id', 'integer');
        $tableB->setPrimaryKey(['id']);

        $tableA = new Table('dbdriver_baz');
        $tableA->addColumn('id', 'integer');
        $tableA->setPrimaryKey(['id']);

        $tableMany = new Table('dbdriver_bar_baz');
        $tableMany->addColumn('bar_id', 'integer');
        $tableMany->addColumn('baz_id', 'integer');
        $tableMany->addForeignKeyConstraint('dbdriver_bar', ['bar_id'], ['id']);

        $metadatas = $this->convertToClassMetadata([$tableA, $tableB], [$tableMany]);

        self::assertCount(1, $metadatas['DbdriverBaz']->getPropertiesIterator(), 'no association mappings should be detected.');
    }

    public function testLoadMetadataFromDatabaseDetail() : void
    {
        if (! $this->em->getConnection()->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $this->markTestSkipped('Platform does not support foreign keys.');
        }

        $table = new Table('dbdriver_foo');

        $table->addColumn('id', 'integer', ['unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addColumn('column_unsigned', 'integer', ['unsigned' => true]);
        $table->addColumn('column_comment', 'string', ['comment' => 'test_comment']);
        $table->addColumn('column_default', 'string', ['default' => 'test_default']);
        $table->addColumn('column_decimal', 'decimal', ['precision' => 4, 'scale' => 3]);

        $table->addColumn('column_index1', 'string');
        $table->addColumn('column_index2', 'string');
        $table->addIndex(['column_index1', 'column_index2'], 'index1');

        $table->addColumn('column_unique_index1', 'string');
        $table->addColumn('column_unique_index2', 'string');
        $table->addUniqueIndex(['column_unique_index1', 'column_unique_index2'], 'unique_index1');

        $this->sm->dropAndCreateTable($table);

        $metadatas = $this->extractClassMetadata(['DbdriverFoo']);

        self::assertArrayHasKey('DbdriverFoo', $metadatas);

        $metadata = $metadatas['DbdriverFoo'];

        self::assertNotNull($metadata->getProperty('id'));

        $idProperty = $metadata->getProperty('id');

        self::assertEquals('id', $idProperty->getName());
        self::assertEquals('id', $idProperty->getColumnName());
        self::assertEquals('integer', $idProperty->getTypeName());

        // FIXME: Condition here is fugly.
        // NOTE: PostgreSQL and SQL SERVER do not support UNSIGNED integer
        if (! $this->em->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform &&
             ! $this->em->getConnection()->getDatabasePlatform() instanceof SQLServerPlatform) {
            self::assertNotNull($metadata->getProperty('columnUnsigned'));

            $columnUnsignedProperty = $metadata->getProperty('columnUnsigned');
            $columnUnsignedOptions  = $columnUnsignedProperty->getOptions();

            self::assertArrayHasKey('unsigned', $columnUnsignedOptions);
            self::assertTrue($columnUnsignedOptions['unsigned']);
        }

        // Check comment
        self::assertNotNull($metadata->getProperty('columnComment'));

        $columnCommentProperty = $metadata->getProperty('columnComment');
        $columnCommentOptions  = $columnCommentProperty->getOptions();

        self::assertArrayHasKey('comment', $columnCommentOptions);
        self::assertEquals('test_comment', $columnCommentOptions['comment']);

        // Check default
        self::assertNotNull($metadata->getProperty('columnDefault'));

        $columnDefaultProperty = $metadata->getProperty('columnDefault');
        $columnDefaultOptions  = $columnDefaultProperty->getOptions();

        self::assertArrayHasKey('default', $columnDefaultOptions);
        self::assertEquals('test_default', $columnDefaultOptions['default']);

        // Check decimal
        self::assertNotNull($metadata->getProperty('columnDecimal'));

        $columnDecimalProperty = $metadata->getProperty('columnDecimal');

        self::assertEquals(4, $columnDecimalProperty->getPrecision());
        self::assertEquals(3, $columnDecimalProperty->getScale());

        // Check indexes
        $indexes = $metadata->table->getIndexes();

        self::assertNotEmpty($indexes['index1']['columns']);
        self::assertEquals(
            ['column_index1','column_index2'],
            $indexes['index1']['columns']
        );

        // Check unique constraints
        $uniqueConstraints = $metadata->table->getUniqueConstraints();

        self::assertNotEmpty($uniqueConstraints['unique_index1']['columns']);
        self::assertEquals(
            ['column_unique_index1', 'column_unique_index2'],
            $uniqueConstraints['unique_index1']['columns']
        );
    }
}
