<?php

namespace pocketcloud\rest\endpoint\impl\server;

use pocketcloud\lib\express\io\Request;
use pocketcloud\lib\express\io\Response;
use pocketcloud\lib\express\route\Router;
use pocketcloud\rest\endpoint\EndPoint;
use pocketcloud\server\CloudServerManager;

class CloudServerExecuteEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::POST, "/server/execute/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $name = $request->data()->queries()->get("server");
        $command = $request->data()->queries()->get("command");
        $server = CloudServerManager::getInstance()->getServerByName($name);

        if ($server === null) {
            return ["error" => "The server doesn't exists!"];
        }

        if (CloudServerManager::getInstance()->sendCommand($server, $command)) {
            return ["success" => "The command was successfully sent to the server!"];
        }
        return ["error" => "The command can't be send to the server!"];
    }

    public function isBadRequest(Request $request): bool {
        if ($request->data()->queries()->has("server") && $request->data()->queries()->has("command")) return false;
        return true;
    }
}