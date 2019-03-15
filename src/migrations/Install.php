<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\migrations;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            // $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
		$this->driver = Craft::$app->getConfig()->getDb()->driver;
		$this->dropForeignKeys();
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%affiliate_credits}}');
        if ($tableSchema === null) {
			$tablesCreated = true;
			
            $this->createTable(
                '{{%affiliate_credits}}',
                [
                    'id' => $this->primaryKey(),
                    'userId' =>  $this->integer()->notNull(),
                    'orderId' =>  $this->integer()->notNull(),
					'totalPrice' => $this->decimal(14, 4)->notNull()->unsigned(),
					'invoiceId' => $this->integer(),
					'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
			);
			
			$this->createTable(
                '{{%affiliate_invoices}}',
                [
					'id' => $this->primaryKey(),
					'number' => $this->string(32),
					'userId' => $this->integer()->notNull(),
					'firstName' => $this->string(),
					'lastName' => $this->string(),
					'address1' => $this->string(),
					'address2' => $this->string(),
					'city' => $this->string(),
					'zipCode' => $this->string(),
					'phone' => $this->string(),
					'alternativePhone' => $this->string(),
					'businessName' => $this->string(),
					'businessTaxId' => $this->string(),
					'businessId' => $this->string(),
					'stateName' => $this->string(),
					'countryId' => $this->integer(),
					'totalPrice' => $this->decimal(14, 4)->defaultValue(0),
					'currency' => $this->string(),
					'paid' => $this->boolean()->notNull()->defaultValue(0),
					'paymentEmail' => $this->string(),
					'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
					'uid' => $this->uid(),
                ]
			);
			
			$this->createTable(
                '{{%affiliate_user}}',
                [
					'id' => $this->primaryKey(),
					'userId' =>  $this->integer()->notNull(),
					'trackingRef' => $this->string(),
					'paymentEmail' => $this->string(),
					'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
					'uid' => $this->uid(),
				]
			);

			$this->createTable(
                '{{%affiliate_order_tracking}}',
                [
					'id' => $this->primaryKey(),
					'orderId' =>  $this->integer()->notNull(),
					'trackingRef' => $this->string(),
					'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
					'uid' => $this->uid(),
				]
			);

		}
		

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
		$this->createIndex(null, '{{%affiliate_credits}}', 'userId', false);
		$this->createIndex(null, '{{%affiliate_credits}}', 'orderId', true);
		$this->createIndex(null, '{{%affiliate_credits}}', 'invoiceId', false);
        $this->createIndex(null, '{{%affiliate_credits}}', 'dateCreated', false);
        $this->createIndex(null, '{{%affiliate_user}}', 'userId', false);
        $this->createIndex(null, '{{%affiliate_user}}', 'trackingRef', false);
        $this->createIndex(null, '{{%affiliate_order_tracking}}', 'orderId', false);
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%affiliate_credits}}', 'userId'),
            '{{%affiliate_credits}}',
            'userId',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
		);

		$this->addForeignKey(
            $this->db->getForeignKeyName('{{%affiliate_user}}', 'userId'),
            '{{%affiliate_user}}',
            'userId',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
		);

		$this->addForeignKey(
            $this->db->getForeignKeyName('{{%affiliate_order_tracking}}', 'orderId'),
            '{{%affiliate_order_tracking}}',
            'orderId',
            '{{%commerce_orders}}',
            'id',
            NULL,
            'CASCADE'
		);
		
    }

	protected function dropForeignKeys()
    {
        MigrationHelper::dropAllForeignKeysOnTable('{{%affiliate_credits}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%affiliate_user}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%affiliate_order_tracking}}', $this);
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%affiliate_credits}}');
        $this->dropTableIfExists('{{%affiliate_invoices}}');
        $this->dropTableIfExists('{{%affiliate_user}}');
        $this->dropTableIfExists('{{%affiliate_order_tracking}}');
    }
}
