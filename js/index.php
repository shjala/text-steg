<?php
function NotFound()
{
    header("HTTP/1.0: 404 Not Found");
    $notfound =
        "<HTML>" .
            "<HEAD>" .
            "<TITLE>404 Not Found</TITLE>" .
            "</HEAD>" .
            "<BODY>" .
            "<H1>Not Found</H1>" .
            "The requested URL " . htmlspecialchars($_SERVER['REQUEST_URI']) . " was not found on this server." .
            "<P>" .
            "<HR>" .
            "<ADDRESS>" .
            "</ADDRESS>" .
            "</BODY>" .
            "</HTML>";
    echo $notfound;
    die();
}
NotFound();
?>