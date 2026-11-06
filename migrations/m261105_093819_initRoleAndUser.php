<?php

use yii\db\Migration;

class m261105_093819_initRoleAndUser extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert(
            '{{%account}}',
            ['username', 'password', 'name', 'role'],
            [
                ['ADM001', Yii::$app->security->generatePasswordHash('admin123'), 'Alex', 'Admin'],
                ['ATH001', Yii::$app->security->generatePasswordHash('author123'), 'John', 'Author'],
            ]
        );

        $auth = Yii::$app->authManager;

        // Buat permission
        $permBlogIndex = $auth->createPermission('post/index');
        $permBlogIndex->description = 'Akses halaman index blog';
        $auth->add($permBlogIndex);

        $permBlogDelete = $auth->createPermission('post/delete');
        $permBlogDelete->description = 'Hapus blog';
        $auth->add($permBlogDelete);

        // Buat role Admin
        $roleAdmin = $auth->createRole('Admin');
        $auth->add($roleAdmin);
        $auth->addChild($roleAdmin, $permBlogIndex);
        $auth->addChild($roleAdmin, $permBlogDelete);
        $auth->assign($roleAdmin, 'ADM001');

        // Buat role Author
        $roleAuthor = $auth->createRole('Author');
        $auth->add($roleAuthor);
        $auth->addChild($roleAuthor, $permBlogIndex);
        $auth->assign($roleAuthor, 'ATH001');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $roleAdmin = $auth->getRole('Admin');
        if ($roleAdmin) {
            $auth->revoke($roleAdmin, 'ADM001');
            $auth->remove($roleAdmin);
        }

        $roleAuthor = $auth->getRole('Author');
        if ($roleAuthor) {
            $auth->revoke($roleAuthor, 'ATH001');
            $auth->remove($roleAuthor);
        }

        $permBlogIndex = $auth->getPermission('post/index');
        if ($permBlogIndex) {
            $auth->remove($permBlogIndex);
        }

        $permBlogDelete = $auth->getPermission('post/delete');
        if ($permBlogDelete) {
            $auth->remove($permBlogDelete);
        }

        $this->delete('{{%account}}', ['username' => ['ADM001', 'ATH001']]);

        return true;
    }
}