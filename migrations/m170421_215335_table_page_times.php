<?php

use yii\db\Migration;

class m170421_215335_table_page_times extends Migration
{
    public function safeUp()
    {
        $this->addColumn('page', 'created_at', 'int');
        $this->addColumn('page', 'updated_at', 'int');
    }

    public function down()
    {
        $this->dropColumn('page', 'created_at');
        $this->dropColumn('page', 'updated_at');
        
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
