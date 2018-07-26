<?php
use yii\helpers\Html;
use yii\grid\GridView;
use common\components\helpers\UserA;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\BoxClassSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Box Classes');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box-class-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <hr/>
    <p>
        <?= UserA::a('<span class="glyphicon glyphicon-refresh"></span> '.Yii::t('app', 'Update Box Classes'), ['update-boxes'], ['class' => 'btn btn-primary']) ?>
    </p>
    <hr/>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'Box',
                'value' => function($model){
                    if(class_exists($model->class)){
                        $box = new $model->class;
                        return $box->getBoxName();
                    }
                }
            ],
            'class',
            [
                'attribute' => 'status',
                'value' => 'statusName',
                'filter' => $searchModel->getStatusList()
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update}'
            ],
        ],
    ]); ?>
</div>