<?php

namespace alexgivi\requireModal;

use yii\base\Widget;
use yii\bootstrap\Html;

class RequireLink extends Widget
{
    /**
     * Текст ссылки.
     *
     * @var string
     */
    public $text = 'Отправить';

    /**
     * url ссылки.
     *
     * @var string|array
     */
    public $url;

    /**
     * Элементы формы диалогового окна.
     * @see RequireHelper
     *
     * @var string|RequireHelper
     */
    public $requireFormItems;

    /**
     * Заголовок диалогового окна.
     *
     * @var string
     */
    public $requireModalTitle = 'Заполните форму';

    /**
     * Html атрибуты ссылки.
     *
     * @var array
     */
    public $options = [];

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function run()
    {
        if (empty($this->url)) {
            throw new RequireModalException('Необходимо указать url');
        }

        if (empty($this->requireFormItems)) {
            throw new RequireModalException('Необходимо указать элементы формы (requireFormItems)');
        }

        $content = '';
        if (!RequireModal::$included) {
            $content .= RequireModal::widget();
            RequireModal::$included = true;
        }

        $options = $this->options;

        $requireParams = $this->requireFormItems;
        if ($requireParams instanceof RequireHelper) {
            $requireParams = $requireParams->getRequireData();
        }

        $options['data-require'] = $requireParams;
        $options['data-modal-title'] = $this->requireModalTitle;

        $content .= Html::a($this->text, $this->url, $options);

        return $content;
    }

    /**
     * @param string               $text
     * @param string|array         $url
     * @param string|RequireHelper $requireItems
     * @param string|null          $modalTitle если не указано - берется $text
     * @param array                $options
     *
     * @return string
     */
    public static function a($text, $url, $requireItems, $modalTitle = null, $options = [])
    {
        if (!$modalTitle) {
            $modalTitle = $text;
        }

        try {
            return self::widget([
                'text' => $text,
                'url' => $url,
                'requireFormItems' => $requireItems,
                'requireModalTitle' => $modalTitle,
                'options' => $options,
            ]);
        } catch (\Exception $e) {
            return '';
        }
    }
}