<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Post */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->can('Admin') && in_array($model->status, [\app\models\Post::STATUS_DRAFT, \app\models\Post::STATUS_REJECTED], true)): ?>
            <?= Html::a('Edit', ['update', 'id' => $model->idpost], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Ajukan review', ['submit', 'id' => $model->idpost], [
                'class' => 'btn btn-success',
                'data' => ['method' => 'post', 'confirm' => 'Ajukan artikel ini untuk review?'],
            ]) ?>
        <?php endif; ?>
    </p>

    <p><strong>Status:</strong> <?= Html::encode($model->getStatusLabel()) ?></p>
    <?php if ($model->status === \app\models\Post::STATUS_REJECTED): ?>
        <div class="alert alert-warning">
            <strong>Alasan penolakan:</strong><br>
            <?= nl2br(Html::encode($model->rejection_reason)) ?>
        </div>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title:ntext',
            'content:html',
            'date',
            'username',
            [
                'attribute' => 'status',
                'value' => $model->getStatusLabel(),
            ],
        ],
    ]) ?>

</div>
