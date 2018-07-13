<?php

namespace alexgivi\requireModal;

class Modal extends \yii\bootstrap\Modal
{
    public $beforeBody = '';
    public $afterFooter = '';

    protected function renderBodyBegin()
    {
        return $this->beforeBody . parent::renderBodyBegin();
    }

    protected function renderFooter()
    {
        return parent::renderFooter() . $this->afterFooter;
    }
}