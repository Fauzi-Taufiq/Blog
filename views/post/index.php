<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$isAdmin = Yii::$app->user->can('Admin');
$this->title = $isAdmin ? 'Review Artikel' : 'Dashboard Artikel Saya';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!$isAdmin): ?>
            <?= Html::a('Buat Artikel', ['create'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'title:ntext',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                },
            ],
            [
                'attribute' => 'rejection_reason',
                'visible' => !$isAdmin,
                'format' => 'ntext',
            ],
            'date',
            'username',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => $isAdmin ? '{view} {approve} {reject}' : '{view} {update} {submit} {delete}',
                'buttons' => [
                    'view' => function ($url) {
                        return Html::a('Lihat', $url, ['class' => 'btn btn-default btn-xs action-link']);
                    },
                    'update' => function ($url, $model) {
                        if (!in_array($model->status, [\app\models\Post::STATUS_DRAFT, \app\models\Post::STATUS_REJECTED], true)) {
                            return '';
                        }
                        return Html::a('Edit', $url, ['class' => 'btn btn-default btn-xs action-link']);
                    },
                    'submit' => function ($url, $model) {
                        if (!in_array($model->status, [\app\models\Post::STATUS_DRAFT, \app\models\Post::STATUS_REJECTED], true)) {
                            return '';
                        }
                        return Html::a('Ajukan review', ['submit', 'id' => $model->idpost], [
                            'class' => 'btn btn-primary btn-xs action-link',
                            'data' => ['method' => 'post', 'confirm' => 'Ajukan artikel ini untuk review?'],
                        ]);
                    },
                    'approve' => function ($url, $model) {
                        return Html::a('Setujui', ['approve', 'id' => $model->idpost], [
                            'class' => 'btn btn-primary btn-xs action-link',
                            'data' => ['method' => 'post', 'confirm' => 'Publikasikan artikel ini?'],
                        ]);
                    },
                    'reject' => function ($url, $model) {
                        return Html::a('Tolak', ['reject', 'id' => $model->idpost], ['class' => 'btn btn-default btn-xs action-link']);
                    },
                    'delete' => function ($url, $model) {
                        if (!in_array($model->status, [\app\models\Post::STATUS_DRAFT, \app\models\Post::STATUS_REJECTED], true)) {
                            return '';
                        }
                        return Html::a('Hapus', $url, [
                            'class' => 'btn btn-default btn-xs action-link',
                            'data' => [
                                'confirm' => 'Hapus artikel ini?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
