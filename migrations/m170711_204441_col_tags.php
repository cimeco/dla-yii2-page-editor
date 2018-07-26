<?php

use yii\db\Migration;

class m170711_204441_col_tags extends Migration
{
    public function up()
    {
        $this->addColumn('col', '_tags', 'varchar(255)');
    }

    public function down()
    {
        $this->dropColumn('col', '_tags');
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
