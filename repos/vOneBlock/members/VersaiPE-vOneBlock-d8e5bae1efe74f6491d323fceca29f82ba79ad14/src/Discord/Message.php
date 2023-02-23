<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Discord;

class Message implements \JsonSerializable {

    private array $data;

    public function setContent(string $content): self {
        $this->data["content"] = $content;
        return $this;
    }

    public function getContent(): ?string {
        return $this->data["content"];
    }

    public function setUsername(string $username): self {
        $this->data["username"] = $username;
        return $this;
    }

    public function getUsername(): ?string {
        return $this->data["username"];
    }

    public function setAvatar(string $url): self {
        $this->data["avatar_url"] = $url;
        return $this;
    }

    public function getAvatar(): ?string {
        return $this->data["avatar_url"];
    }

    public function addEmbed(Embed $embed): self {
        if(!empty(($arr = $embed->asArray()))) {
            $this->data["embeds"][] = $arr;
        }
        return $this;
    }

    public function JsonSerialize() {
        return $this->data;
    }

}