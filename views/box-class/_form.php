<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model quoma\pageeditor\models\BoxClass */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-class-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStatusList()) ?>
    
    <?= $form->field($model, 'roles')->checkboxList(ArrayHelper::map(Role::getAvailableRoles(), 'name', 'description'), [
        'separator' => '<br>'
    ]) ?>

    <hr/>
            
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>