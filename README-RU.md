yii2-require-modal
==================
Виджет для добавления диалоговых окон с формой к ссылкам.


Установка
---------

Предпочтительный способ - [composer](http://getcomposer.org/download/).

Выполнить

```
php composer.phar require --prefer-dist alexgivi/yii2-require-modal "*"
```

или добавить

```
"alexgivi/yii2-require-modal": "*"
```

в секцию require файла `composer.json`.


Использование
-------------

Виджет ссылки с формой:

```php
<?= \alexgivi\requireModal\RequireLink::widget([
    'text' => 'Заполнить форму'
    'url' => ['test-controller/test-require'],
    'requireFormItems' => \alexgivi\requireModal\RequireHelper::compose()
         ->addDateField('date', 'Дата', date('d.m.Y'))
         ->addNumberField('number', 'Число')
         ->addTextField('text', 'Текст')
         ->addTextArea('textarea', 'Большой текст')
         ->addDropDownList('selected', 'Список', SomeClass::$someData)
]); ?>
```

В контроллере:

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

Использование с моделями
------------------------

Можно использовать компонент для реализации формы
создания / редактирования моделей в диалоговом окне.

Виджет ссылки с формой:

```php
/**
 * @var $model SomeModel
 */

 ...

<?= \alexgivi\requireModal\RequireLink::widget([
    'text' => 'Изменить'
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

В контроллере:

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

Для создания новой модели:

```php
/**
 * @var $model SomeModel
 */

<?php $model = new SomeModel(); ?>
<?= \alexgivi\requireModal\RequireLink::widget([
    'text' => 'Создать'
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

В контроллере:

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
