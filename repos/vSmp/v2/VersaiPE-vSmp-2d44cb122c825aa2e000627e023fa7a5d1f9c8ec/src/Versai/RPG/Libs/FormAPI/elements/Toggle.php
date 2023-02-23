<?php

namespace Versai\RPG\Libs\FormAPI\elements;

use Versai\RPG\Libs\FormAPI\window\WindowForm;

class Toggle extends ElementCustom
{

    /** @var boolean */
    private $default = false;


    public function __construct(WindowForm $form, String $name, String $text, bool $default)
    {
        parent::__construct($form, $name, $text);
        $this->default = $default;

        $this->content = [
            "type" => "toggle",
            "text" => $this->text,
            "default" => $this->default
        ];
    }

    /**
     * @return bool
     */
    public function getValue(): bool
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function getFinalValue(): bool
    {
        return parent::getFinalValue();
    }

}