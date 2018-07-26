<?php

use yii\db\Migration;

class m170316_183947_box_table extends Migration
{
    public function safeUp()
    {
        
        $this->createTable('box', [
            'box_id' => $this->primaryKey(),
            'col_id' => $this->integer(),
            'box_class' => $this->string(255),
            'order' => $this->integer(),
        ]);
        
        $this->createIndex('box_order', 'box', 'order');
        $this->addForeignKey('fk_col_id', 'box', 'col_id', 'col', 'col_id');
        
        $this->createTable('box_revision', [
            'box_revision_id' => $this->primaryKey(),
            'box_id' => $this->integer(),
            'session' => $this->string(255),
            'timestamp' => $this->integer(),
            'active' => $this->boolean(),
            'style' => $this->string(255),
            'css_class' => $this->string(255),
            '_data' => $this->text()
        ]);
        
        $this->createIndex('box_revision_session', 'box_revision', 'session');
        $this->addForeignKey('fk_box_revision_box_id', 'box_revision', 'box_id', 'box', 'box_id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_col_id', 'box');
        $this->dropForeignKey('fk_box_revision_box_id', 'box_revision');
        $this->dropTable('box_revision');
        $this->dropTable('box');
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
