<?php declare(strict_types=1);

namespace vOT\db;

use Closure;
use vOT\libs\poggit\libasynql\{DataConnector, libasynql};
use vOT\Loader;

class Database {
    private DataConnector $db;
    
    public function __construct(Loader $plugin) {
        $this->db = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
            "mysql" => "vot.sql"
        ]);
    }

    public function close(): void {
        if(isset($this->db)) $this->db->close();
    }

    public function init(): void {
        $this->db->executeGeneric("vot.init", []);
    }

    public function getRawTime(string $username, Closure $fn): void {
        $this->db->executeGeneric("vot.getTime", ["username" => $username], function(array $rows) use ($fn): void {
            $fn(isset($rows[0]) ? $rows[0] : 0);
        });
    }

    public function getTotalTime(string $username, Closure $fn): void {
        $this->db->executeGeneric("vot.getTotalTime", ["username" => $username], function(array $rows) use ($fn): void {
            if(isset($rows[0])) { $fn(gmdate("H:i:s", $rows[0])); }
        });
    }

    public function getSessionTime(string $username): string {
        if(!Loader::$TIMES[$username]) return 0;
        return gmdate("H:i:s", time() - Loader::$TIMES[$username]);
    }

    public function getLastSeen(string $username, Closure $fn): void {
        $this->db->executeGeneric("vot.getLastSeen", ["username" => $username], function(array $rows) use ($fn): void {
            if(isset($rows[0])) {
                $time = $rows[0];
                $fn(gmdate("Y-m-d H:i:s", $time));
            } else {
                $fn("Never");
            }
        });
    }

    public function hasTime(string $username, Closure $fn): void {
        $this->db->executeGeneric("vot.hasTime", ["username" => $username], function(array $rows) use ($fn): void {
            $fn(isset($rows[0]));
        });
    }

    public function updateTime(string $username, int $time): void {
        $this->db->executeGeneric("vot.updateTime", ["username" => $username, "time" => $time]);
    }

    public function updateLastSeen(string $username, int $time): void {
        $this->db->executeGeneric("vot.updateLastSeen", ["username" => $username, "time" => $time]);
    }

    public function getTop(Closure $fn): void {
		$this->database->executeSelect('vtime.getTop', [], function(array $rows) use ($fn): void {
			$fn($rows);
		});
	}

    public function deleteAll(): void {
		$this->database->executeGeneric('vtime.deleteAll');
	}

    public function getDB(): DataConnector {
        return $this->db;
    }
}