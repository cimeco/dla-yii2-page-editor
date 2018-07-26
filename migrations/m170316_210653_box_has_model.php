<?php

use yii\db\Migration;

class m170316_210653_box_has_model extends Migration
{
    public function safeUp()
    {
        $this->createTable('box_revision_has_model', [
            'box_revision_has_model_id' => $this->primaryKey(),
            'box_revision_id' => $this->integer(),
            'model_class' => $this->string(255),
            'model_id' => $this->integer()
        ]);
        
        $this->createIndex('model_class_index', 'box_revision_has_model', 'model_class');
        $this->createIndex('model_id_index', 'box_revision_has_model', 'model_id');
        $this->addForeignKey('fk_box_revision_id', 'box_revision_has_model', 'box_revision_id', 'box_revision', 'box_revision_id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_box_revision_id', 'box_revision_has_model');
        $this->dropIndex('model_id_index', 'box_revision_has_model');
        $this->dropIndex('model_class_index', 'box_revision_has_model');
        
        $this->dropTable('box_revision_has_model');
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
