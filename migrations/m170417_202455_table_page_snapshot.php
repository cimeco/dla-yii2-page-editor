<?php

use yii\db\Migration;

class m170417_202455_table_page_snapshot extends Migration
{
    public function up()
    {
        $this->createTable('snapshot', [
            'snapshot_id' => $this->primaryKey(),
            'page_id' => $this->integer()->notNull(),
            'timestamp' => $this->integer(),
            'status' => $this->integer(),
        ]);
        
        $this->addForeignKey('fk_page_id', 'snapshot', 'page_id', 'page', 'page_id');
        
        $this->createIndex('index_timestamp', 'snapshot', 'timestamp');
        $this->createIndex('index_status', 'snapshot', 'status');
    }

    public function down()
    {
        $this->dropTable('snapshot');
        return true;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
