<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Theme */

$this->title = Yii::t('app', 'Git pull from repo');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Themes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="theme-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_pull', [
        'pull' => $pull,
    ]) ?>

</div>

<div class="pull-output">
    <?php if($output): ?>
    <h4>Output: </h4>
    <pre><?php
        foreach($output as $line){
            echo $line ."\n";
        }
        ?>
    </pre>
    <?php endif; ?>
</div>
