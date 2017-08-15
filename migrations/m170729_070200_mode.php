<?php
use yii\db\Migration;

class m170729_070200_mode extends Migration
{
    public function up()
    {
        $this->createTable('{{%mode}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(32)->notNull()->unique(),
            'name' => $this->string(64)->notNull()->unique(),
        ]);
        $this->batchInsert('{{%mode}}', [ 'key', 'name' ], [
            [ 'nawabari',    'Turf War' ],
            [ 'area',        'Splat Zones' ],
            [ 'yagura',      'Tower Control' ],
            [ 'hoko',        'Rainmaker' ],
        ]);
        $this->createTable('{{%lobby}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(32)->notNull()->unique(),
            'name' => $this->string(64)->notNull()->unique(),
        ]);
        $this->batchInsert('{{%lobby}}', [ 'key', 'name' ], [
            [ 'regular', 'Regular' ],
            [ 'ranked', 'Ranked' ],
            [ 'league', 'League' ],
            [ 'fest', 'Splatfest' ],
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%lobby}}');
        $this->dropTable('{{%mode}}');
    }
}
