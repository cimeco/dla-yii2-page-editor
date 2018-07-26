<?php

use yii\db\Migration;

class m170330_140420_col_has_boxes extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_col_id', 'box');
        $this->dropColumn('box', 'col_id');
        $this->dropColumn('box', 'order');
        
        $this->createTable('col_has_boxes', [
            //Utilizamos el primaryKey para ordenar:
            'col_has_boxes_id' => $this->primaryKey(),
            
            'col_id' => $this->integer(),
            'box_id' => $this->integer()
        ]);
        
        $this->addForeignKey('fk_col_has_boxes_col_id', 'col_has_boxes', 'col_id', 'col', 'col_id');
        $this->addForeignKey('fk_col_has_boxes_box_id', 'col_has_boxes', 'box_id', 'box', 'box_id');
    }

    public function down()
    {
        $this->dropTable('col_has_boxes');
        
        $this->addColumn('box', 'order', $this->integer());
        $this->addColumn('box', 'col_id', $this->integer());
        $this->addForeignKey('fk_col_id', 'box', 'col_id', 'col', 'col_id');
        
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
