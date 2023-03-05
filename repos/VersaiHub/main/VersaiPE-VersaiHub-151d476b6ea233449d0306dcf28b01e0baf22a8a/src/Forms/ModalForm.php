<?php

declare(strict_types = 1);

namespace Versai\Forms;

use pocketmine\form\FormValidationException;

class ModalForm extends Form {

    private string $content = "";

    public function __construct(?callable $callable) {
        parent::__construct($callable);
        $this->data["type"] = "modal";
        $this->data["title"] = "";
        $this->data["content"] = $this->content;
        $this->data["button1"] = "";
        $this->data["button2"] = "";
    }

    public function processData(&$data) : void {
        if(!is_bool($data)) {
            throw new FormValidationException("Expected return type bool, got " . gettype($data));
        }
    }

    public function setTitle(string $title) : void {
        $this->data["title"] = $title;
    }

    public function getTitle() : string {
        return $this->data["title"];
    }

    public function getContent() : string {
        return $this->data["content"];
    }

    public function setContent(string $content) : void {
        $this->data["content"] = $content;
    }

    public function setButton1(string $text) : void {
        $this->data["button1"] = $text;
    }

    public function getButton1() : string {
        return $this->data["button1"];
    }

    public function setButton2(string $text) : void {
        $this->data["button2"] = $text;
    }

    public function getButton2() : string {
        return $this->data["button2"];
    }

}