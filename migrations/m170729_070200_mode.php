<?php
use yii\db\Migration;

class m170729_070200_mode extends Migration
{
    public function up()
    {
        $this->createTable('{{%mode_group}}', [
            'id'        => $this->integer()->notNull(),
            'name'      => $this->string(64)->notNull()->unique(),
            'PRIMARY KEY ([[id]])',
        ]);
        $this->createTable('{{%mode}}', [
            'id'        => $this->primaryKey(),
            'group_id'  => $this->integer()->notNull(),
            'key'       => $this->string(16)->notNull()->unique(),
            'name'      => $this->string(64)->notNull()->unique(),
            'FOREIGN KEY ([[group_id]]) REFERENCES {{%mode_group}}([[id]])',
        ]);
        $this->batchInsert('{{%mode_group}}', [ 'id', 'name' ], [
            [ 1, 'regular' ],
            [ 2, 'gachi' ],
            [ 3, 'salmon' ],
        ]);
        $this->batchInsert('{{%mode}}', [ 'group_id', 'key', 'name' ], [
            [ 1, 'nawabari',    'Turf War' ],
            [ 2, 'area',        'Splat Zones' ],
            [ 2, 'yagura',      'Tower Control' ],
            [ 2, 'hoko',        'Rainmaker' ],
            [ 3, 'salmon',      'Salmon Run' ],
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%mode}}');
        $this->dropTable('{{%mode_group}}');
    }
}
