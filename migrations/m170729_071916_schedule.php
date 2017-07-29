<?php
use yii\db\Migration;

class m170729_071916_schedule extends Migration
{
    public function up()
    {
        $this->createTable('{{%regular}}', [
            'id'            => $this->primaryKey(),
            'start_at'      => $this->integer()->notNull()->unique(),
            'end_at'        => $this->integer()->notNull()->unique(),
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
        ]);
        $this->createTable('{{%ranked}}', [
            'id'            => $this->primaryKey(),
            'mode_id'       => $this->integer()->notNull(),
            'start_at'      => $this->integer()->notNull()->unique(),
            'end_at'        => $this->integer()->notNull()->unique(),
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
            'FOREIGN KEY ([[mode_id]]) REFERENCES {{%mode}}([[id]])',
        ]);
        $this->createTable('{{%league}}', [
            'id'            => $this->primaryKey(),
            'mode_id'       => $this->integer()->notNull(),
            'start_at'      => $this->integer()->notNull()->unique(),
            'end_at'        => $this->integer()->notNull()->unique(),
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
            'FOREIGN KEY ([[mode_id]]) REFERENCES {{%mode}}([[id]])',
        ]);
        $this->createTable('{{%salmon}}', [
            'id'            => $this->primaryKey(),
            'start_at'      => $this->integer()->notNull()->unique(),
            'end_at'        => $this->integer()->notNull()->unique(),
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
        ]);
        foreach (['regular', 'ranked', 'league', 'salmon'] as $gameMode) {
            $this->createTable("{{%{$gameMode}_stage}}", [
                'schedule_id'   => $this->integer()->notNull(),
                'stage_id'      => $this->integer()->notNull(),
                'PRIMARY KEY ([[schedule_id]], [[stage_id]])',
                "FOREIGN KEY ([[schedule_id]]) REFERENCES {{%{$gameMode}}}([[id]])",
                "FOREIGN KEY ([[stage_id]]) REFERENCES {{%stage}}([[id]])",
            ]);
        }
    }

    public function down()
    {
        foreach (['regular', 'ranked', 'league', 'salmon'] as $gameMode) {
            $this->dropTable("{{%{$gameMode}_stage}}");
            $this->dropTable("{{%{$gameMode}}}");
        }
    }
}
