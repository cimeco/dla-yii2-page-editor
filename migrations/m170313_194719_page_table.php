<?php

use yii\db\Migration;

class m170313_194719_page_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('page', [
            'page_id' => $this->primaryKey(),
            'slug' => $this->string(45),
            'name' => $this->string(45),
            'status' => $this->integer(),
            'theme_id' => $this->integer()
        ]);
        
        $this->createIndex('page_status_index', 'page', ['status']);
        
        $this->addForeignKey('fk_page_theme_id', 'page', 'theme_id', 'theme', 'theme_id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_page_theme_id', 'page');
        $this->dropIndex('page_status_index', 'page');
        $this->dropTable('page');
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
