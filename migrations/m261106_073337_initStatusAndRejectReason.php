<?php

use yii\db\Migration;

class m261106_073337_initStatusAndRejectReason extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%post}}', 'status', $this->string(20)->notNull()->defaultValue('published'));
        $this->addColumn('{{%post}}', 'rejection_reason', $this->text()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%post}}', 'rejection_reason');
        $this->dropColumn('{{%post}}', 'status');
    }
}
