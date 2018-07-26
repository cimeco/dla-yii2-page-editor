<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Theme */

$this->title = Yii::t('app', 'Create Theme');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Themes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="theme-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'pull' => $pull,
        'output' => $output
    ]) ?>

</div>
