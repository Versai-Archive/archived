<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Forms;

class CustomForm extends Form {

	private $labelMap = [];

	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "custom_form";
		$this->data["title"] = "";
		$this->data["content"] = [];
	}

	public function processData(&$data) : void {
		if(is_array($data)) {
			$new = [];
			foreach ($data as $i => $v) {
				$new[$this->labelMap[$i]] = $v;
			}
			$data = $new;
		}
	}

	public function setTitle(string $title) : void {
		$this->data["title"] = $title;
	}

	public function getTitle() : string {
		return $this->data["title"];
	}

	public function addLabel(string $text, ?string $label = null) : void {
		$this->addContent(["type" => "label", "text" => $text]);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addToggle(string $text, bool $default = null, ?string $label = null) : void {
		$content = ["type" => "toggle", "text" => $text];
		if($default !== null) {
			$content["default"] = $default;
		}
		$this->addContent($content);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addSlider(string $text, int $min, int $max, int $step = -1, int $default = -1, ?string $label = null) : void {
		$content = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
		if($step !== -1) {
			$content["step"] = $step;
		}
		if($default !== -1) {
			$content["default"] = $default;
		}
		$this->addContent($content);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addStepSlider(string $text, array $steps, int $defaultIndex = -1, ?string $label = null) : void {
		$content = ["type" => "step_slider", "text" => $text, "steps" => $steps];
		if($defaultIndex !== -1) {
			$content["default"] = $defaultIndex;
		}
		$this->addContent($content);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addDropdown(string $text, array $options, int $default = null, ?string $label = null) : void {
		$this->addContent(["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default]);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addInput(string $text, string $placeholder = "", string $default = null, ?string $label = null) : void {
		$this->addContent(["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default]);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	private function addContent(array $content) : void {
		$this->data["content"][] = $content;
	}

}