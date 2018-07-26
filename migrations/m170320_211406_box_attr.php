<?php

use yii\db\Migration;

class m170320_211406_box_attr extends Migration
{
    public function safeUp()
    {
        $this->createTable('box_revision_attr', [
            'box_revision_attr_id' => $this->primaryKey(),
            'box_revision_id' => $this->integer(),
            'attr' => $this->string(45),
            'type' => $this->string(45),
            'value' => $this->text()
        ]);
        
        $this->addForeignKey('fk_box_attr_box_revision_id', 'box_revision_attr', 'box_revision_id', 'box_revision', 'box_revision_id');
        $this->createIndex('box_attr_attr_index', 'box_revision_attr', 'attr');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_box_attr_box_revision_id', 'box_revision_attr');
        $this->dropTable('box_revision_attr');
        
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
