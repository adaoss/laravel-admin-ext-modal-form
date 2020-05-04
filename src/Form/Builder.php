<?php


namespace Encore\ModalForm\Form;


use Encore\Admin\Facades\Admin;

class Builder extends \Encore\Admin\Form\Builder
{
    protected $view = 'modal-form::form';

    /**
     * Width for label and field.
     *
     * @var array
     */
    protected $width = [
        'label' => 3,
        'field' => 8,
    ];

    /**
     * @var ModalForm
     */
    protected $form;

    /**
     * @return string
     */
    public function render():string
    {
        return json_encode([
            'content' => view($this->view, $this->getData())->render(),
            'script' => str_replace('data-exec-on-popstate', 'data-exec-on-modal-load', Admin::script()->render())
        ]);
    }

    /**
     * Do initialize.
     */
    public function init()
    {
        parent::init();
        $this->getTools()
            ->disableList()
            ->disableView()
            ->disableDelete();
        $this->footer = new Footer($this);
    }

    public function open($options = []): string
    {
        $open = parent::open($options);
        return str_replace('pjax-container', '', $open);
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $this->removeReservedFields();

        $tabObj = $this->form->setTab();

        if(!$tabObj->isEmpty()){
            $script = $this->getScript();
            Admin::script($script);
        }

        return [
            'form'   => $this,
            'size'   => $this->form->getSize(),
            'tabObj' => $tabObj,
            'width'  => $this->width,
            'layout' => $this->form->getLayout(),
        ];
    }

    /**
     * @return string
     */
    protected function getScript(){
        return <<<'SCRIPT'

var hash = document.location.hash;
if (hash) {
    $('.nav-tabs a[href="' + hash + '"]').tab('show');
}

// Change hash for page-reload
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    history.pushState(null,null, e.target.hash);
});

if ($('.has-error').length) {
    $('.has-error').each(function () {
        var tabId = '#'+$(this).closest('.tab-pane').attr('id');
        $('li a[href="'+tabId+'"] i').removeClass('hide');
    });

    var first = $('.has-error:first').closest('.tab-pane').attr('id');
    $('li a[href="#'+first+'"]').tab('show');
}

SCRIPT;
    }
}
