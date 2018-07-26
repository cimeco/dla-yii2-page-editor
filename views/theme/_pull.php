<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="pull-form">
    
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($pull, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($pull, 'password')->passwordInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
</div>
