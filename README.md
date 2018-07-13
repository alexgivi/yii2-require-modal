yii2-require-modal
==================
Widget for add form in modal dialog to links.


Install
-------

Preferrable way - via [composer](http://getcomposer.org/download/).

Execute

```
php composer.phar require --prefer-dist alexgivi/yii2-require-modal "*"
```

or add

```
"alexgivi/yii2-require-modal": "*"
```

to require section of `composer.json` file.


Usage
-----

Widget for link with form:

```php
<?= \alexgivi\requireModal\RequireLink::widget([
    'text' => 'Complete form'
    'url' => ['test-controller/test-require'],
    'requireFormItems' => \alexgivi\requireModal\RequireHelper::compose()
         ->addDateField('date', 'Date', date('d.m.Y'))
         ->addNumberField('number', 'Number')
         ->addTextField('text', 'Text')
         ->addTextArea('textarea', 'Big Text')
         ->addDropDownList('selected', 'List', SomeClass::$someData)
]); ?>
```

In controller:

```php
class TestController extends yii\web\Controller
{
    public function actionTestRequire()
    {
        $date = Yii::$app->request->post('date');
        $number = Yii::$app->request->post('number');
        $text = Yii::$app->request->post('text');
        // ...
    }
}
```

Usage with models
-----------------

Component can be used to implement model create / update forms in modal.

Widget for link with form:

```php
/**
 * @var $model SomeModel
 */

 ...

<?= \alexgivi\requireModal\RequireLink::widget([
    'text' => 'Update'
    'url' => ['some-model/update', 'id' => $model->id],
    'requireFormItems' => \alexgivi\requireModal\RequireHelper::compose()
         ->addActiveDateField($model, 'date_attribute',)
         ->addNumberField($model, 'number_field')
         ->addTextField($model, 'name')
         ->addTextArea($model, 'description')
         ->addDropDownList($model, 'type', SomeModel::$typeNames)
         ->addHiddenField('redirect', Yii::$app->request->url)
]); ?>
```

In controller:

```php
class SomeModelController extends yii\web\Controller
{
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);

        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(isset($_POST['redirect']) ?
                $_POST['redirect'] : ['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }
}
```

For create new model:

```php
/**
 * @var $model SomeModel
 */

<?php $model = new SomeModel(); ?>
<?= \alexgivi\requireModal\RequireLink::widget([
    'text' => 'Create'
    'url' => ['some-model/create', 'id' => $model->id],
    'requireFormItems' => \alexgivi\requireModal\RequireHelper::compose()
         ->addActiveDateField($model, 'date_attribute',)
         ->addNumberField($model, 'number_field')
         ->addTextField($model, 'name')
         ->addTextArea($model, 'description')
         ->addDropDownList($model, 'type', SomeModel::$typeNames)
         ->addHiddenField('redirect', Yii::$app->request->url)
]); ?>
```

In controller:

```php
class SomeModelController extends yii\web\Controller
{
    public function actionCreate()
    {
        $model = new SomeModel();

        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(isset($_POST['redirect']) ?
                $_POST['redirect'] : ['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }
}
```