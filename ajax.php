<?php

if (is_ajax()) {
    if (isset($_POST["cmd"]) && !empty($_POST["cmd"])) {
        $cmd = $_POST["cmd"];
        if ($cmd == "ping") {
//            $return = $_POST;
            $return["data"] = "pong";
            echo json_encode($return);
        }
    } else {
        echo "Ojoj!";
    }
}

function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>
