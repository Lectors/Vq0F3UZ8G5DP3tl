<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%posts}}`.
 */
class m251012_153137_create_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
            'is_deleted' => $this->boolean()->defaultValue(0),
            'author' => $this->string(15)->notNull()->comment('Имя автора'),
            'email' => $this->string(255)->notNull()->comment('Email'),
            'message' => $this->text()->notNull()->comment('Сообщение'),
            'ip_address' => $this->string(39)->notNull()->comment('IP'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer()->null(),
            'manage_token' => $this->string(255)->notNull()->comment('Токен для редактирования'),
        ]);

        $this->createIndex('idx_posts_ip', '{{%posts}}', ['ip_address']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%posts}}');
    }
}
