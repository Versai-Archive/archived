<?php

declare(strict_types=1);

namespace Versai\Tasks;

use Versai\Main;
use Versai\Query\Query;
use Versai\Query\QueryException;

class NavigationDataTask extends CustomRepeatingTask {

    public function onRun(): void {
        $config = Main::getInstance()->getConfig();

        Main::getInstance()->navigatorButtonInfo = [];

        $serverOnline = null;
        $serverMax = null;

        foreach($config->getNested("NavigatorServers") as $server => $data){
            Main::getInstance()->navigatorAddressInfo[$server] = $data;

            try {
                $query = Query::query($data["address"], $data["port"]);
                $serverOnline = $query["Players"];
                $serverMax = $query["MaxPlayers"];
            } catch (QueryException $exception){
                $serverOnline = -9999;
                $serverMax = 0;
            }

            if($serverOnline !== -9999) {
                Main::getInstance()->navigatorButtonInfo[$server]["bd"] = ucwords($server) . " [" . $serverOnline . "/" . $serverMax . "]\n§7Click to connect...";
            } else {
                Main::getInstance()->navigatorButtonInfo[$server]["bd"] = ucwords($server) . " [0/" . $serverMax . "]\n§4Server Offline...";
            }
            Main::getInstance()->navigatorButtonInfo[$server]["img"] = $data["img"];
        }
    }

}