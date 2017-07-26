<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model app\models\Client */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-form">

    <?php $form = ActiveForm::begin(['id' => $model->formName()]); ?>

    <?= $form->field($model, 'fio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->widget(MaskedInput::className(),[
        'mask' => '89999999999',
    ]) ?>

    <?= $form->field($model, 'email')->widget(MaskedInput::className(), [
            'clientOptions' => ['alias' => 'email'],
    ]) ?>

    <?= $form->field($model, 'street')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'home')->textInput(['type' => 'number', 'min' => 0]) ?>

    <?= $form->field($model, 'apartment')->textInput(['type' => 'number', 'min' => 0]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Редактировать', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php $script = <<<JS
$('form#{$model->formName()}').on('beforeSubmit', function(e) {
  var form = $(this);
  $.post(
      form.attr('action'),
      form.serialize()
  )
    .done(function(result) {
      if (result == true)
          {
              $(document).find('#modalCreateClient').modal('hide');
              $.pjax.reload({container: '#pjax-select'});
          } else {
            $('.client-form').html(result);
          }
    }).fail(function() {
      console.log('server error');
    });
  return false;
})
JS;
$this->registerJs($script);?>