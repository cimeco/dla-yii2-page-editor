<?php

use yii\db\Migration;

class m170328_211315_col_timestamp extends Migration
{
    public function up()
    {
        $this->addColumn('col', 'timestamp', 'int');
        $this->createIndex('col_timestamp_index', 'col', 'timestamp');
    }

    public function down()
    {
        $this->dropColumn('col', 'timestamp');
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
