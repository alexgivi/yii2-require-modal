<?php

namespace alexgivi\requireModal;

use yii\base\Widget;
use yii\bootstrap\Html;

class RequireModal extends Widget
{
    /**
     * Заголовок диалога.
     *
     * @var string
     */
    public $modalLabel = 'Заполните форму';

    /**
     * Текст кнопки отмены - закрытия диалога в футере.
     *
     * @var string
     */
    public $cancelButtonText = 'Отмена';

    /**
     * Текст кнопки сабмита формы.
     *
     * @var string
     */
    public $submitButtonText = 'Отправить';

    /**
     * url картинки лоадера на кнопку сабмита формы после нажатия.
     * если не указан - лоадера не будет.
     *
     * @var string|null
     */
    public $loaderImageUrl = null;

    /**
     * для внутреннего использования.
     * виджет диалогового окна добавляется только 1 раз в страницу.
     *
     * @var bool
     */
    public static $included = false;

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function run()
    {
        RequireModalAsset::register($this->view);

        if ($this->loaderImageUrl) {
            $this->view->registerJs("RequireModal.loaderImageUrl = '$this->loaderImageUrl';");
        }

        $this->view->registerJs('RequireModal.init();');

        $content = Modal::widget([
            'id' => 'require-modal',
            'header' => '<h4 class="modal-title" id="require-modal-label">' . $this->modalLabel . '</h4>',
            'beforeBody' => Html::beginForm('', 'post', ['enctype' => "multipart/form-data"]),
            'footer' => <<<footer
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    $this->cancelButtonText
                </button>
                <button type="submit" id="require-modal-submit"
                        class="btn btn-primary btn-primary-muted disable-on-submit loader-on-submit">
                    $this->submitButtonText
                </button>
footer
            ,
            'afterFooter' => Html::endForm(),
        ]);

        return $content;
    }
}