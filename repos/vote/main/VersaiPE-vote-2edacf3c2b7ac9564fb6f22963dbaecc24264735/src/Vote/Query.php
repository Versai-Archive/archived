<?php

namespace Vote;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

/**
 * @property String url
 * @property String user
 * @property mixed return
 * @property String type
 */
class Query extends AsyncTask{

    /**
     * This query is used to
     * return, the response of
     * the website:
     * https://minecraftpocket-servers.com/
     *
     * Query constructor.
     * @param String $user
     * @param String $type
     * @param String $url
     */
    public function __construct(String $user, String $type, String $url){
        $this->user = $user; 

        $this->type = $type;
        $this->url = $url;

        $this->return = null;
    }
    
    public function onRun() {
        $query = curl_init($this->url);

        curl_setopt($query, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($query, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($query, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($query, CURLOPT_FRESH_CONNECT, 1);

        curl_setopt($query, CURLOPT_AUTOREFERER, true);
        curl_setopt($query, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($query, CURLOPT_HTTPHEADER, array("User-Agent: VoteReward"));

        curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($query, CURLOPT_TIMEOUT, 5);

        $this->return = curl_exec($query);

        curl_close($query);

    }

    /**
     * This function send the result
     * of the query in the plugin.
     *
     * @param Server $server
     */
    public function onCompletion(Server $server){
        $server->getPluginManager()->getPlugin("Vote")->Result($this->user, $this->type, (int)$this->return);
    }


}
