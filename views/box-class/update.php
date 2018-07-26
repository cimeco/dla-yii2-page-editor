<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model quoma\pageeditor\models\BoxClass */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Box',
]) . $box->getBoxName();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Box Classes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $box->getBoxName(), 'url' => ['view', 'id' => $model->box_class_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="box-class-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr/>
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>