<?php
use yii\db\Migration;

class m170816_101104_update_index extends Migration
{
    public function up()
    {
        $this->createIndex('ix_schedule_2', '{{%schedule}}', ['lobby_id', 'end_at']);
    }

    public function down()
    {
        $this->dropIndex('ix_schedule_2', '{{%schedule}}');
    }
}
