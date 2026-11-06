<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Post */

$this->title = 'Tolak Artikel: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Review Artikel', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-reject">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::encode($model->title) ?></p>

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'rejection_reason')->textarea([
        'name' => 'rejection_reason',
        'value' => $model->rejection_reason,
        'rows' => 5,
    ])->label('Alasan penolakan') ?>
    <div class="form-group">
        <?= Html::submitButton('Tolak Artikel', ['class' => 'btn btn-danger']) ?>
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
