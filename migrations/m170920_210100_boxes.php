<?php

use yii\db\Migration;

class m170920_210100_boxes extends Migration
{
    public function safeUp()
    {
        $this->createTable('box_class', [
            'box_class_id' => $this->primaryKey(),
            'class' => $this->string(255),
            'status' => $this->integer()
        ]);
        
        $this->createTable('box_class_has_role', [
            'box_class_id' => $this->integer(),
            'auth_item_name' => $this->string()
        ]);
        
        $this->addForeignKey('fk_box_class_id', 'box_class_has_role', 'box_class_id', 'box_class', 'box_class_id');
        
    }

    public function safeDown()
    {
        $this->dropTable('box_class_has_role');
        $this->dropTable('box_class');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170920_210100_boxes cannot be reverted.\n";

        return false;
    }
    */
}
