<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types = 1);

namespace Versai\OneBlock\Forms;

class SimpleForm extends Form {

	const IMAGE_TYPE_PATH = 0;
	const IMAGE_TYPE_URL = 1;

	private string $content = "";

	private array $labelMap = [];

	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "form";
		$this->data["title"] = "";
		$this->data["content"] = $this->content;
		$this->data["buttons"] = [];
	}

	public function processData(&$data): void {
		$data = $this->labelMap[$data] ?? null;
	}

	public function setTitle(string $title): void {
		$this->data["title"] = $title;
	}

	public function getTitle(): string {
		return $this->data["title"];
	}

	public function getContent(): string {
		return $this->data["content"];
	}

	public function setContent(string $content): void {
		$this->data["content"] = $content;
	}

	public function addButton(string $text, int $imageType = -1, string $imagePath = "", ?string $label = null): void {
		$content = ["text" => $text];
		if($imageType !== -1) {
			$content["image"]["type"] = $imageType === 0 ? "path" : "url";
			$content["image"]["data"] = $imagePath;
		}
		$this->data["buttons"][] = $content;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	// Used for when we need to make buttons without images, making use of labels!
	public function addButtonNoImage(string $text, string $label): void {
		$content = ["text" => $text];
		$this->data["buttons"][] = $content;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

}