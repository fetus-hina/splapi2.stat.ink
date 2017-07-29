<?php
use yii\db\Migration;

class m170729_065226_stage extends Migration
{
    public function up()
    {
        $this->createTable('{{%stage}}', [
            'id'    => $this->primaryKey(),
            'key'   => $this->string(16)->notNull()->unique(),
            'name'  => $this->string(64)->notNull()->unique(),
        ]);
        $this->batchInsert('{{%stage}}', [ 'key', 'name' ], [
            [ 'battera',    'The Reef' ],
            [ 'fujitsubo',  'Musselforge Fitness' ],
            [ 'gangaze',    'Starfish Mainstage' ],
            [ 'combu',      'Humpback Pump Track' ],
            [ 'ama',        'Inkblot Art Academy' ],
            [ 'tachiuo',    'Moray Towers' ],
            [ 'hokke',      'Port Mackerel' ],
            [ 'chozame',    'Sturgeon Shipyard' ],
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%stage}}');
    }
}
