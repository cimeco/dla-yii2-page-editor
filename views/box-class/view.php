<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\components\helpers\UserA;

/* @var $this yii\web\View */
/* @var $model quoma\pageeditor\models\BoxClass */

$this->title = $box->getBoxName();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Box Classes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="box-class-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <hr/>
    <p>
        <?= UserA::a('<span class="glyphicon glyphicon-pencil"></span> '.Yii::t('app', 'Update'), ['update', 'id' => $model->box_class_id], ['class' => 'btn btn-primary']) ?>
        <?= UserA::a('<span class="glyphicon glyphicon-trash"></span> '.Yii::t('app', 'Delete'), ['delete', 'id' => $model->box_class_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <hr/>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'box_class_id',
            'class',
            'statusName',
        ],
    ]) ?>

</div>