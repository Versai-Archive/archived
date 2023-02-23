<?php

declare(strict_types=1);

namespace Versai\RPG\Libs\Discord;

class Embed {

    /** @var array */
    protected $data = [];

    const AQUA = 1752220;
    const DARK_AQUA = 1146986;
    const GREEN = 3066993;
    const DARK_GREEN = 2067276;
    const BLUE = 3447003;
    const DARK_BLUE = 2123412;
    const PURPLE = 10181046;
    const DARK_PURPLE = 7419530;
    const LUMINOUS_VIVID_PINK = 15277667;
    const DARK_VIVID_PINK = 11342935;
    const GOLD = 15844367;
    const DARK_GOLD = 12745742;
    const ORANGE = 15105570;
    const DARK_ORANGE = 11027200;
    const RED = 15158332;
    const DARK_RED = 10038562;
    const GREY = 9807270;
    const DARK_GREY = 9936031;
    const DARKER_GREY = 8359053;
    const LIGHT_GREY = 12370112;
    const NAVY = 3426654;
    const DARK_NAVY = 2899536;
    const YELLOW = 16776960;

    public function asArray(): array{
        return $this->data;
    }

    public function setAuthor(string $name, string $url = null, string $iconURL = null):void{
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
    }

    public function setTitle(string $title):void{
        $this->data["title"] = $title;
    }

    public function setDescription(string $description):void{
        $this->data["description"] = $description;
    }

    public function setColor(int $color):void{
        $this->data["color"] = $color;
    }

    public function addField(string $name, string $value, bool $inline = false):void{
        if(!isset($this->data["fields"])){
            $this->data["fields"] = [];
        }
        $this->data["fields"][] = [
            "name" => $name,
            "value" => $value,
            "inline" => $inline,
        ];
    }

    public function setThumbnail(string $url):void{
        if(!isset($this->data["thumbnail"])){
            $this->data["thumbnail"] = [];
        }
        $this->data["thumbnail"]["url"] = $url;
    }

    public function setImage(string $url):void{
        if(!isset($this->data["image"])){
            $this->data["image"] = [];
        }
        $this->data["image"]["url"] = $url;
    }

    public function setFooter(string $text, string $iconURL = null):void{
        if(!isset($this->data["footer"])){
            $this->data["footer"] = [];
        }
        $this->data["footer"]["text"] = $text;
        if($iconURL !== null){
            $this->data["footer"]["icon_url"] = $iconURL;
        }
    }

    public function setTimestamp(\DateTime $timestamp):void{
        $timestamp->setTimezone(new \DateTimeZone("UTC"));
        $this->data["timestamp"] = $timestamp->format("Y-m-d\TH:i:s.v\Z");
    }

}