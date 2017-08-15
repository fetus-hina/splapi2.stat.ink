<?php
use yii\db\Migration;

class m170729_065226_stage extends Migration
{
    public function up()
    {
        $this->createTable('{{%stage}}', [
            'id'        => $this->integer()->notNull()->unique(),
            'key'       => $this->string(32)->unique(),
            'name'      => $this->string(64)->unique(),
            'image_url' => $this->string(256),
            'local_path' => $this->string(64),
            'PRIMARY KEY ([[id]])',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%stage}}');
    }
}
