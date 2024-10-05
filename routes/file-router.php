<?php

require_once __DIR__ . "/../lib/routepass/routers.php";
require_once __DIR__ . "/../lib/path.php";

require_once __DIR__ . "/Middleware.php";



$file_router = new Router();



$file_router->get("/:file", [
    function (Request $request, Response $response) {
        $path = FILES_DIR . "/" . $request->param->get("file");

        $type = Response::getMimeType($path)
            ->forwardFailure($response)
            ->getSuccess();

        if (strpos($type, "image/") !== false) {
            $response->sendOptimalImage($path, $request);
        } else {
            $response->readFile($path);
        }
    },
], ["file" => Router::REGEX_ANY]);



return $file_router;