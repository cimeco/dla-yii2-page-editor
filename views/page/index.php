<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\components\helpers\UserA;

/* @var $this yii\web\View */
/* @var $searchModel quoma\pageeditor\models\search\PageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Pages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <hr/>
    <p>
        <?= UserA::a('<span class="glyphicon glyphicon-plus"></span> '.Yii::t('app', 'Create Page'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <hr/>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'page_id',
            'name',
            'slug',
            'statusName',
            [
                'attribute' => 'theme_id',
                'value' => function($model){
                    return Html::a($model->theme->name, ['/page-editor/theme/view', 'id' => $model->theme_id]);
                },
                'format' => 'html'
            ],

            ['class' => \quoma\core\grid\ActionColumn::className()],
        ],
    ]); ?>
</div>
