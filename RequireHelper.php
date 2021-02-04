<?php

namespace alexgivi\requireModal;

use JsonException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * класс для облегчения работы с require модальными окнами
 * Class RequireHelper
 */
class RequireHelper
{
    // *********************** атрибуты элемента ***********************

    /** подпись к полю (или текст) */
    protected const PARAM_LABEL = 'label';

    /** имя поля (имя переменной в $_POST[]) (есть не у всех типов) */
    protected const PARAM_NAME = 'name';

    /** тип поля */
    protected const PARAM_TYPE = 'type';

    /** предустановленное значение поля - не обязательно */
    protected const PARAM_VALUE = 'value';

    /**
     * настройки видимости поля - не обязательно
     *
     * в качестве значения можно передать массив вида
     * [
     *  'name1' => <value1>,
     *  'name2' => <value2>,
     *  'name3' => [<value1>, <value2>]
     * ]
     * в этом случае элемент становится видимым,
     * только если другие элементы формы будут иметь заданные значения.
     * если в качестве значения передан массив, значит значение поля
     * должно быть равно одному из переданных значений.
     * иначе элемент в форме не отображается
     */
    protected const PARAM_VISIBLE = 'visible';

    /** дополнительные параметры - не обязательно */
    protected const PARAM_OPTIONS = 'options';

    // *********************** дополнительные параметры ***********************

    /**
     * bool
     * поле обязательно для заполнения - не обязательно
     */
    protected const OPTION_REQUIRED = 'required';

    /**
     * int
     * минимальная длина текста в поле ввода тип textarea - не обязательно
     */
    protected const OPTION_MIN_LENGTH = 'minlength';

    /**
     * array
     * для списков - массив элементов вида [
     *   <value1> => <text1>
     *   <value2> => <text2>
     * ]
     * можно использовать CHtml::listData() для заполнения
     * обязательное поле для типов select, radioList, checkBoxList
     * для других типов не учитывается
     */
    protected const OPTION_ITEMS = 'items';

    /**
     * int
     * размер поля выбора нескольких элементов - не обязательно
     * для типа select
     */
    protected const OPTION_SIZE = 'size';

    /**
     * bool
     * возможность выбора нескольких элементов - не обязательно
     * для типов select, file
     */
    protected const OPTION_MULTIPLE = 'multiple';

    /**
     * bool
     * отображение списков горизонтально - не обязательно
     * для типов radioList, checkBoxList
     */
    protected const OPTION_INLINE = 'inline';

    /**
     * bool
     * выбран ли чекбокс - не обязательно
     * для типа checkbox
     */
    protected const OPTION_CHECKED = 'checked';

    /**
     * int
     * минимальное значение - не обязательно
     * для типа number, date, time
     */
    protected const OPTION_MIN = 'min';

    // *********************** типы полей формы ***********************

    /** блок с текстом */
    protected const FIELD_TYPE_DIV = 'div';

    /** заголовок */
    protected const FIELD_TYPE_HEADER = 'header';

    /** скрытое поле */
    protected const FIELD_TYPE_HIDDEN = 'hidden';

    /** числовое поле */
    protected const FIELD_TYPE_NUMBER = 'number';

    /** текстовое поле */
    protected const FIELD_TYPE_TEXT = 'text';

    /** выбор даты */
    protected const FIELD_TYPE_DATE = 'date';

    /** выбор времени */
    protected const FIELD_TYPE_TIME = 'time';

    /** выбор даты и времени */
    protected const FIELD_TYPE_DATETIME = 'datetime';

    /** поле ввода большого текста */
    protected const FIELD_TYPE_TEXT_AREA = 'textarea';

    /** чекбокс. можно указать опции checked, value */
    protected const FIELD_TYPE_CHECKBOX = 'checkbox';

    /**
     * выпадающий список
     * обязательно надо задать options[items]
     */
    protected const FIELD_TYPE_SELECT = 'select';

    /**
     * список с возможностью выбора нескольких элементов
     * обязательно надо задать options[items]
     * можно задать value в виде массива выбранных элементов
     */
    protected const FIELD_TYPE_CHECKBOX_LIST = 'checkBoxList';

    /**
     * список с возможностью выбора одного элемента
     * обязательно надо задать options[items]
     */
    protected const FIELD_TYPE_RADIO_LIST = 'radioList';

    /** файл. можно задать опцию multiple */
    protected const FIELD_TYPE_FILE = 'file';

    private array $fields = [];

    public static function compose(): self
    {
        return new static();
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getRequireData(): string
    {
        return json_encode($this->fields, JSON_THROW_ON_ERROR);
    }

    protected static function filterOptions($options): ?array
    {
        if (empty($options)) {
            return null;
        }
        return array_filter(
            $options,
            static function ($value) {
                return $value !== null && $value !== false;
            }
        );
    }

    protected function addField(
        $type,
        $label = null,
        $name = null,
        $options = null,
        $value = null,
        $visible = null
    ): self {
        $item = [];
        if ($label) {
            $item[self::PARAM_LABEL] = $label;
        }

        if ($type) {
            $item[self::PARAM_TYPE] = $type;
        }

        if ($name) {
            $item[self::PARAM_NAME] = $name;
        }

        if ($value) {
            $item[self::PARAM_VALUE] = $value;
        }

        if (!empty($options)) {
            $item[self::PARAM_OPTIONS] = $options;
        }

        if ($visible) {
            $item[self::PARAM_VISIBLE] = $visible;
        }

        $this->fields[] = $item;
        return $this;
    }

    public function addTextField($name, $label, $value = null, $required = true, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_TEXT,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                ]
            ),
            $value,
            $visible
        );
    }

    public function addTextArea($name, $label = 'Комментарий', $required = true, $minLength = 20, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_TEXT_AREA,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_MIN_LENGTH => $minLength,
                ]
            ),
            null,
            $visible
        );
    }

    public function addTextAreaWithValue(
        string $name,
        string $label = 'Комментарий',
        string $value = '',
        bool $required = true,
        $visible = null,
        array $options = []
    ): self {
        $options[self::OPTION_REQUIRED] = $required;
        return $this->addField(
            self::FIELD_TYPE_TEXT_AREA,
            $label,
            $name,
            self::filterOptions($options),
            $value,
            $visible
        );
    }

    public function addNumberField($name, $label, $value = null, $required = true, $min = 0): self
    {
        return $this->addField(
            self::FIELD_TYPE_NUMBER,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_MIN => $min,
                ]
            ),
            $value
        );
    }

    public function addDateField($name, $label = 'Дата', $value = null, $required = true, $visible = null, $min = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_DATE,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_MIN => $min,
                ]
            ),
            $value,
            $visible
        );
    }

    public function addTimeField($name, $label = 'Время', $value = null, $required = false, $min = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_TIME,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_MIN => $min,
                ]
            ),
            $value
        );
    }

    public function addDateTimeField($name, $label, $required = false, $value = null, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_DATETIME,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                ]
            ),
            $value,
            $visible
        );
    }

    public function addDropDownList($name, $label, $items, $value = null, $required = true, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_SELECT,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_ITEMS => $items,
                ]
            ),
            $value,
            $visible
        );
    }

    public function addDropDownListWithPrompt(
        $name,
        $label,
        $items,
        $prompt,
        $value = null,
        $required = true,
        $visible = null
    ): self {
        $items = ArrayHelper::merge([null => $prompt], $items);

        return $this->addField(
            self::FIELD_TYPE_SELECT,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_ITEMS => $items,
                ]
            ),
            $value,
            $visible
        );
    }

    public function addCheckBox($name, $label, $options = null, $value = null, $visible = null): self
    {
        return $this->addField(self::FIELD_TYPE_CHECKBOX, $label, $name, $options, $value, $visible);
    }

    public function addCheckBoxList($name, $label, $items, $value = null, $required = true, $inline = true): self
    {
        return $this->addField(
            self::FIELD_TYPE_CHECKBOX_LIST,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_ITEMS => $items,
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_INLINE => $inline,
                ]
            ),
            $value
        );
    }

    public function addRadioList($name, $label, $items, $value = null, $required = true, $inline = false): self
    {
        return $this->addField(
            self::FIELD_TYPE_RADIO_LIST,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_ITEMS => $items,
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_INLINE => $inline,
                ]
            ),
            $value
        );
    }

    public function addFileField($name, $label, $multiple = true, $required = true): self
    {
        return $this->addField(
            self::FIELD_TYPE_FILE,
            $label,
            $name,
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required,
                    self::OPTION_MULTIPLE => $multiple,
                ]
            )
        );
    }

    public function addHiddenField($name, $value = null): self
    {
        return $this->addField(self::FIELD_TYPE_HIDDEN, null, $name, null, $value);
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param bool|null $required
     * @param array|null $visible
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveTextField($model, $attribute, $required = null, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_TEXT,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                ]
            ),
            $model->$attribute,
            $visible
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param bool|null $required
     * @param int $minLength
     * @param array|null $visible
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveTextArea($model, $attribute, $required = null, $minLength = 20, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_TEXT_AREA,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_MIN_LENGTH => $minLength,
                ]
            ),
            $model->$attribute,
            $visible
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param bool|null $required
     * @param int $min
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveNumberField($model, $attribute, $required = null, $min = 0): self
    {
        return $this->addField(
            self::FIELD_TYPE_NUMBER,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_MIN => $min,
                ]
            ),
            $model->$attribute
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param bool|null $required
     * @param array|null $visible
     * @param string $min
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveDateField($model, $attribute, $required = null, $visible = null, $min = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_DATE,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_MIN => $min,
                ]
            ),
            $model->$attribute,
            $visible
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param bool|null $required
     * @param string $min
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveTimeField($model, $attribute, $required = null, $min = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_TIME,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_MIN => $min,
                ]
            ),
            $model->$attribute
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param bool|null $required
     * @param array|null $visible
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveDateTimeField($model, $attribute, $required = null, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_DATETIME,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                ]
            ),
            $model->$attribute,
            $visible
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $items
     * @param bool|null $required
     * @param array|null $visible
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveDropDownList($model, $attribute, $items, $required = null, $visible = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_SELECT,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_ITEMS => $items,
                ]
            ),
            $model->$attribute,
            $visible
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $items
     * @param string $prompt
     * @param bool|null $required
     * @param array|null $visible
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveDropDownListWithPrompt(
        $model,
        $attribute,
        $items,
        $prompt,
        $required = null,
        $visible = null
    ): self {
        $items = ArrayHelper::merge([null => $prompt], $items);

        return $this->addField(
            self::FIELD_TYPE_SELECT,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_ITEMS => $items,
                ]
            ),
            $model->$attribute,
            $visible
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array|null $options
     * @param array|null $visible
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveCheckBox($model, $attribute, $options = null, $visible = null): self
    {
        if (empty($options['checked'])) {
            $options['checked'] = $model->$attribute;
        }

        return $this->addField(
            self::FIELD_TYPE_CHECKBOX,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            $options,
            1,
            $visible
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $items
     * @param bool|null $required
     * @param bool $inline
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveCheckBoxList($model, $attribute, $items, $required = null, $inline = true): self
    {
        return $this->addField(
            self::FIELD_TYPE_CHECKBOX_LIST,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute][]",
            self::filterOptions(
                [
                    self::OPTION_ITEMS => $items,
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_INLINE => $inline,
                ]
            ),
            $model->$attribute
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $items
     * @param bool|null $required
     * @param bool $inline
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveRadioList($model, $attribute, $items, $required = null, $inline = false): self
    {
        return $this->addField(
            self::FIELD_TYPE_RADIO_LIST,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute]",
            self::filterOptions(
                [
                    self::OPTION_ITEMS => $items,
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_INLINE => $inline,
                ]
            ),
            $model->$attribute
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param bool $multiple
     * @param bool $required
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveFileField($model, $attribute, $multiple = true, $required = null): self
    {
        return $this->addField(
            self::FIELD_TYPE_FILE,
            $model->getAttributeLabel($attribute),
            $model->formName() . "[$attribute][]",
            self::filterOptions(
                [
                    self::OPTION_REQUIRED => $required === null ? $model->isAttributeRequired($attribute) : $required,
                    self::OPTION_MULTIPLE => $multiple,
                ]
            )
        );
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     *
     * @return RequireHelper
     *
     * @throws InvalidConfigException
     */
    public function addActiveHiddenField($model, $attribute): self
    {
        return $this->addField(
            self::FIELD_TYPE_HIDDEN,
            null,
            $model->formName() . "[$attribute]",
            null,
            $model->$attribute
        );
    }

    public function addHeader($label): self
    {
        return $this->addField(self::FIELD_TYPE_HEADER, $label);
    }

    public function addDiv($label): self
    {
        return $this->addField(self::FIELD_TYPE_DIV, $label);
    }
}