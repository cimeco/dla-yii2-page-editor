<?php
use yii\helpers\Url;
?>

<div class="box-list">
    <?php foreach($boxes as $box): ?>
    <div class="editor-box">
        <div class="box-name-col">
            <?= $box->boxName ?>
            <span class="help-block">
                <?= $box->boxDescription ?>
            </span>
        </div>
        <div class="box-select-col">
            <?php $url = Url::to(['/page-editor/box/create'], 'https'); ?>
            <a class="btn btn-primary" data-add-box data-class="<?= $box->className() ?>" data-action="<?= $url ?>">
                <span class="glyphicon glyphicon-plus"></span>
                <?= Yii::t('app', 'Select') ?>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>