<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\Theme */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Themes');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="theme-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Theme'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'theme_id',
            'name',
            'slug',
            [
                'attribute' => 'status',
                'value' => function($model){ return $model->getStatusName(); },
                'filter' => $searchModel->getStatusList()
            ],
            'basePath',
            // 'baseUrl:url',
            // 'public',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Repo',
                'template' => '{pull}',
                'buttons' => [
                    'pull' => function ($url, $model, $key) {
                        return $model->gitUrl ? Html::a('<span class="glyphicon glyphicon-download"></span> Git Pull', $url) : null;
                    }
                ]
            ],
            [
                'class' => \quoma\core\grid\ActionColumn::className(),
            ],
        ],
    ]); ?>
</div>
