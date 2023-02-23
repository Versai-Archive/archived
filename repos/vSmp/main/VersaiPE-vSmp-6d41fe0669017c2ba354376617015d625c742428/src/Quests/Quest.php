<?php

declare(strict_types = 1);

namespace Versai\RPGCore\Quests;

use pocketmine\network\mcpe\protocol\types\BossBarColor;

class   Quest {

    const QUEST_DIFFICULTY_EASY = "easy";
    const QUEST_DIFFICULTY_MEDIUM = "medium";
    const QUEST_DIFFICULTY_HARD = "hard";

    // Quest types

    const QUEST_TYPE_BLOCK_BREAK = "block_break"; // done
    const QUEST_TYPE_BLOCK_PLACE = "block_place"; // done
    const QUEST_TYPE_COLLECT = "collect";
    const QUEST_TYPE_KILL_MOB = "kill_mob"; // done
    const QUEST_TYPE_KILL_MOB_HOSTILE = "kill_mob_hostile";
    const QUEST_TYPE_KILL_PLAYER = "kill_player";
    const QUEST_TYPE_PLAY_TIME = "play_time";
    const QUEST_TYPE_CRAFT_ITEM = "craft_item";
    const QUEST_TYPE_INTERACT_NPC = "interact_npc";

    
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $visual;
    /** @var string */
    public $description;
    /** @var string MUST BE "easy", "medium" or "hard" */
    public $difficulty;
    /** @var int */
    public $required;


    public function __construct(int $id, string $name, string $visual, string $description, string $type, string $difficulty, int $required, string $reqId = null) {
        $this->id = $id;
        $this->name = $name;
        $this->visual = $visual;
        $this->description = $description;
        $this->type = $type;
        $this->difficulty = $difficulty;
        $this->required = $required;
        /** The ID of the Var needed ex. Block ID, Item ID Etc. */
        $this->reqId = $reqId;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getVisual(): string {
        return $this->visual;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getDifficulty(): string {
        return $this->difficulty;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setVisual(string $visual): void {
        $this->visual = $visual;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getRequirement(): int {
        return $this->required;
    }

    public function getBarColor(): int {
        
        switch($this->difficulty) {
            case Quest::QUEST_DIFFICULTY_EASY:
                return BossBarColor::GREEN;
            break;

            case Quest::QUEST_DIFFICULTY_MEDIUM:
                return BossBarColor::YELLOW;
            break;

            case Quest::QUEST_DIFFICULTY_HARD:
                return BossBarColor::RED;
            break;

            default:
                return BossBarColor::PURPLE;
        }
    }
}