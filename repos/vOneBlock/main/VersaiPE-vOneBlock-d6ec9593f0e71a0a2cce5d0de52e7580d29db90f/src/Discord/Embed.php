<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Discord;

class Embed {

    private array $data;

    public function asArray(): array {
        return $this->data;
    }

    public function setAuthor(string $name, string $url = null, string $iconURL = null): self {
		if(!isset($this->data["author"])){
			$this->data["author"] = [];
		}
		$this->data["author"]["name"] = $name;
		if($url !== null){
			$this->data["author"]["url"] = $url;
		}
		if($iconURL !== null){
			$this->data["author"]["icon_url"] = $iconURL;
		}
        return $this;
	}

	public function setTitle(string $title): self {
		$this->data["title"] = $title;
        return $this;
	}

	public function setDescription(string $description): self {
		$this->data["description"] = $description;
        return $this;
	}

	public function setColor(int $color): self {
		$this->data["color"] = $color;
        return $this;
	}

	public function addField(string $name, string $value, bool $inline = false): self {
		if(!isset($this->data["fields"])){
			$this->data["fields"] = [];
		}
		$this->data["fields"][] = [
			"name" => $name,
			"value" => $value,
			"inline" => $inline,
		];
        return $this;
	}

	public function setThumbnail(string $url): self {
		if(!isset($this->data["thumbnail"])){
			$this->data["thumbnail"] = [];
		}
		$this->data["thumbnail"]["url"] = $url;
        return $this;
	}

	public function setImage(string $url): self {
		if(!isset($this->data["image"])){
			$this->data["image"] = [];
		}
		$this->data["image"]["url"] = $url;
        return $this;
	}

	public function setFooter(string $text, string $iconURL = null): self {
		if(!isset($this->data["footer"])){
			$this->data["footer"] = [];
		}
		$this->data["footer"]["text"] = $text;
		if($iconURL !== null){
			$this->data["footer"]["icon_url"] = $iconURL;
		}
        return $this;
	}

	public function setTimestamp(\DateTime $timestamp): self {
		$timestamp->setTimezone(new \DateTimeZone("UTC"));
		$this->data["timestamp"] = $timestamp->format("Y-m-d\TH:i:s.v\Z");
        return $this;
	}

}