<?php

use yii\db\Migration;

class m170310_184048_col_table extends Migration
{
    public function safeUp()
    {
        
        $this->createTable('col', [
            'col_id' => $this->primaryKey(),
            'slug' => $this->string(45),
            'uid' => $this->string(255),
            'order' => $this->integer(),
            'devices' => $this->string(100),
            'class' => $this->string(45),
            'style' => $this->string(255),
        ]);
        
        $this->createIndex('col_slug_index', 'col', ['slug']);
        $this->createIndex('col_uid_index', 'col', ['uid']);
        $this->createIndex('col_order_index', 'col', ['order']);
        
    }

    public function safeDown()
    {
        $this->dropIndex('col_slug_index', 'col');
        $this->dropIndex('col_uid_index', 'col');
        $this->dropIndex('col_order_index', 'col');
        $this->dropTable('col');
        
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
