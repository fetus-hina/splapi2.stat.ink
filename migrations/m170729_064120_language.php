<?php
use yii\db\Migration;

class m170729_064120_language extends Migration
{
    public function up()
    {
        $this->createTable('language', [
            'id'    => $this->primaryKey(),
            'code'  => $this->string(8)->notNull()->unique(),
            'name'  => $this->string(32)->notNull()->unique(),
        ]);

        $this->batchInsert('language', ['code', 'name'], [
            [ 'ja-JP', '日本語' ],
            [ 'en-US', 'English (America)' ],
            [ 'en-GB', 'English (Europe)' ],
        ]);
    }

    public function down()
    {
        $this->dropTable('language');
    }
}
