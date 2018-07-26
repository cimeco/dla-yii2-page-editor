<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model quoma\pageeditor\models\Page */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Page',
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->page_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="page-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr/>
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
