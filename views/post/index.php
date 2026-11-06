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
                    'update' => function ($url, $model) {
                        if (!in_array($model->status, [\app\models\Post::STATUS_DRAFT, \app\models\Post::STATUS_REJECTED], true)) {
                            return '';
                        }
                        return Html::a('Edit', $url, ['class' => 'btn btn-primary btn-xs']);
                    },
                    'submit' => function ($url, $model) {
                        if (!in_array($model->status, [\app\models\Post::STATUS_DRAFT, \app\models\Post::STATUS_REJECTED], true)) {
                            return '';
                        }
                        return Html::a('Ajukan review', ['submit', 'id' => $model->idpost], [
                            'class' => 'btn btn-success btn-xs',
                            'data' => ['method' => 'post', 'confirm' => 'Ajukan artikel ini untuk review?'],
                        ]);
                    },
                    'approve' => function ($url, $model) {
                        return Html::a('Approve', ['approve', 'id' => $model->idpost], [
                            'class' => 'btn btn-success btn-xs',
                            'data' => ['method' => 'post', 'confirm' => 'Publikasikan artikel ini?'],
                        ]);
                    },
                    'reject' => function ($url, $model) {
                        return Html::a('Reject', ['reject', 'id' => $model->idpost], ['class' => 'btn btn-danger btn-xs']);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
