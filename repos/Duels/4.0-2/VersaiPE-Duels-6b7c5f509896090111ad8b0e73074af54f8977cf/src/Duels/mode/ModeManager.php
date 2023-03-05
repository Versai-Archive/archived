<?php


namespace Duels\mode;


use Duels\Loader;

class ModeManager
{

    /**
     * @var Loader
     */
    private $own;

    private array $modes = [];

    public function __construct()
    {

        $this->own = Loader::getInstance();

        $this->init();

    }

    public function init()
    {

        foreach ($this->own->modes->getAll() as $index => $value) {

           $this->modes[$index] = new Mode($index, $value);

        }

    }

    public function getMode(string $mode):Mode
    {

        return $this->modes[$mode];

    }

    public function modeExists(string $mode):bool
    {

        return isset($this->modes[$mode]);

    }

}