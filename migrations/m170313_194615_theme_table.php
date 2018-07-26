<?php

use yii\db\Migration;

class m170313_194615_theme_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('theme', [
            'theme_id' => $this->primaryKey(),
            'slug' => $this->string(255),
            'name' => $this->string(250),
            'status' => $this->integer(),
            'basePath' => $this->string(255),
            'baseUrl' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
        
        $this->createIndex('theme_slug_index', 'theme', ['slug']);
    }

    public function safeDown()
    {
        $this->dropIndex('theme_slug_index', 'theme');
        $this->dropTable('theme');
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
