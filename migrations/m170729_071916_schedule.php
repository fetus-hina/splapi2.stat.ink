<?php
use yii\db\Migration;

class m170729_071916_schedule extends Migration
{
    public function up()
    {
        $this->createTable('{{%schedule}}', [
            'id' => $this->primaryKey(),
            'lobby_id' => $this->integer()->notNull(),
            'mode_id' => $this->integer()->notNull(),
            'start_at' => $this->integer()->notNull(),
            'end_at' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[lobby_id]]) REFERENCES {{%lobby}}([[id]])',
            'FOREIGN KEY ([[mode_id]]) REFERENCES {{%mode}}([[id]])',
            'UNIQUE ([[lobby_id]], [[start_at]])',
        ]);
        $this->createIndex('ix_schedule_1', '{{%schedule}}', ['lobby_id', 'start_at']);
        $this->createTable("{{%schedule_stage}}", [
            'schedule_id' => $this->integer()->notNull(),
            'stage_id' => $this->integer()->notNull(),
            'PRIMARY KEY ([[schedule_id]], [[stage_id]])',
            "FOREIGN KEY ([[schedule_id]]) REFERENCES {{%schedule}}([[id]])",
            "FOREIGN KEY ([[stage_id]]) REFERENCES {{%stage}}([[id]])",
        ]);
    }

    public function down()
    {
        $this->dropTable("{{%schedule_stage}}");
        $this->dropTable("{{%schedule}}");
    }
}
