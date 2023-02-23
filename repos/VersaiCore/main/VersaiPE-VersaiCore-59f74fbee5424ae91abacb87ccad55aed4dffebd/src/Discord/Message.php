<?php

declare(strict_types=1);

namespace Versai\Discord;

use JsonSerializable;

class Message implements JsonSerializable
{
	/** @var array */
	protected $data = [];

	public function setContent(string $content): self {
		$this->data["content"] = $content;
		return $this;
	}

	public function getContent(): ?string {
		return $this->data["content"];
	}

	public function getUsername(): ?string {
		return $this->data["username"];
	}

	public function setUsername(string $username): self {
		$this->data["username"] = $username;
		return $this;
	}

	public function getAvatarURL(): ?string {
		return $this->data["avatar_url"];
	}

	public function setAvatarURL(string $avatarURL): self {
		$this->data["avatar_url"] = $avatarURL;
		return $this;
	}

	public function addEmbed(Embed $embed): self {
		if (!empty(($arr = $embed->asArray()))) {
			$this->data["embeds"][] = $arr;
		}
		return $this;
	}

	public function jsonSerialize()
	{
		return $this->data;
	}
}