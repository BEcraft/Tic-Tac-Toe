<?php

define("SERVER", "http://" . $_SERVER["HTTP_HOST"] . "/" . basename(dirname(__FILE__)) . "/");


include(__DIR__ . "/sessions/Player.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** POST */
if (!empty($_POST)) {
    if (isset($_POST["#1"]) and isset($_POST["#2"])) {

        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!empty($_SESSION) and ($_POST["#1"] !== $_SESSION["one"] or $_POST["#2"] !== $_SESSION["two"])) {
                if (session_destroy()) {
                    session_start();
                }
            }
        }

        $_SESSION["one"] = new Player(trim(strip_tags($_POST["#1"])));
        $_SESSION["one"]->setColour("red");

        $_SESSION["two"] = new Player(trim(strip_tags($_POST["#2"])));
        $_SESSION["two"]->setColour("green");

        if (strlen($_SESSION["one"]->getName()) === 0 or strlen($_SESSION["two"]->getName()) === 0) {
            finish(true);
        }

    }
}

if (isset($_SESSION) or ($_SESSION["one"] and $_SESSION["two"])) {

    if (!isset($_SESSION["turn"])) {
        $_SESSION["turn"] = 1;
    }

    if (!isset($_SESSION["table"])) {
        $_SESSION["table"] = array(
            0, 0, 0,
            0, 0, 0,
            0, 0, 0,
        ); //opción: array_fill
    }

    /** GET */
    if (!empty($_GET)) {
        $play = array_keys($_GET)[0];
        if (strpos($play, "play") !== false) {
            $play = str_replace("play", "", $play);
            if (is_numeric($_SESSION["table"][$play]) and !isset($_SESSION["over"])) {
                $_SESSION["table"][$play] = ($_SESSION["turn"] % 2) === 0 ? "x" : "o";
                $_SESSION["turn"]++;
            }
        } else {
            if ($play === "reset") {
                finish(true);
            }
        }
    }

    if (($_SESSION["turn"] % 2) === 0) {
        $user = $_SESSION["two"];
    } else {
        $user = $_SESSION["one"];
    }

    $html = array(
        "<div>" => "<div class='play'>",
        "<turn>" => "<h3> Turn: <span style='color: " . $user->getColour() . ";'>" . $user->getName() . "</span></h3><br />",
        "<form>" => "<form action='" . SERVER . "Playground.php' method='GET' id='center'><table border='5' rols='3'><tr>"
    );
    for ($i = 0; $i < 9; ++$i) {

        if ($i !== 0 and ($i % 3) === 0) {
            $html["<form>"] .= "</tr>" . ($i !== 8 ? "<tr>" : "");
        }

        if ($_SESSION["table"][$i] !== 0) {
            $value = $_SESSION["table"][$i];
        } else {
            $value = "  ";
        }

        if ($_SESSION["table"][$i] !== 0) {
            if ($_SESSION["table"][$i] === "x") {
                $colour = "green";
            } else {
                $colour = "red";
            }
        }

        $html["<form>"] .= "<td><input type='submit' name='play{$i}' value='{$value}' style='color: " . ($colour ?? "black") . ";'></td>";

        if ($i === 8) {
            $html["<form>"] .= "</table><input type='submit' name='reset' id='center' value='RESET'>";
            $html["<form>"] .= "</form></div>";
        }

    }

} else {
    finish(true);
}

function finish($session = false) {
    if ($session) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    header("Location: " . SERVER . "index.html");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title> Playground : Tic Tac Toe </title>
        <link rel="icon" href="images/icon.png">
        <link rel="stylesheet" type="text/css" href="css/Game.css">
        <link rel="stylesheet" type="text/css" href="css/Style.css">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no">
    </head>
    <body>
        <?php

        if (($_SESSION["turn"] - 1 % 2) === 0) {
            $lastPlayer = $_SESSION["two"];
        } else {
            $lastPlayer = $_SESSION["one"];
        }

        if ($lastPlayer) {
            for ($i = 0; $i < 9; ++$i) {

                /** Vertical
                 *   ^ ^ ^
                 *   | | |
                 *   v v v
                 */
                if ($i <= 2) {
                    $counter = array();
                    $compass = $i;
                    for ($o = 0; $o < 3; ++$o) {
                        if ($_SESSION["table"][$compass] !== 0 and $_SESSION["table"][$compass] === $_SESSION["table"][$i]) {
                            $counter[] = $compass;
                        }
                        $compass += 3;
                    }
                    if (count($counter) === 3) {
                        foreach($counter as $index) {
                            $html["<form>"] = str_replace("name='play{$index}'", "name='play{$index}' style='background-color:rgba(100, 200, 50, 0.5); color:white;' ", $html["<form>"]);
                        }
                        $over = "Vertical";
                        break;
                    }
                }

                /** Horizontal
                 *    < -- >
                 *    < -- >
                 *    < -- >
                 */
                if ($i === 0 or $i === 3 or $i === 6) {
                    $counter = array();
                    for ($o = $i; $o < ($i + 3); ++$o) {
                        if ($_SESSION["table"][$o] !== 0 and $_SESSION["table"][$o] === $_SESSION["table"][$i]) {
                            $counter[] = $o;
                        }
                    }
                    if (count($counter) === 3) {
                        foreach($counter as $index) {
                            $html["<form>"] = str_replace("name='play{$index}'", "name='play{$index}' style='background-color:rgba(100, 200, 50, 0.5); color:white;' ", $html["<form>"]);
                        }
                        $over = "Horizontal";
                        break;
                    }
                }

                /** Diagonal
                 *   \   /
                 *     x
                 *   /   \
                 */
                if ($i === 0 or $i === 2) {
                    $counter = array();
                    $compass = $i;
                    for ($o = 0; $o < 3; ++$o) {
                        if ($_SESSION["table"][$compass] !== 0 and $_SESSION["table"][$compass] === $_SESSION["table"][$i]) {
                            $counter[] = $compass;
                        }
                        $compass += ($i === 0) ? 4 : 2;
                    }
                    if (count($counter) === 3) {
                        foreach($counter as $index) {
                            $html["<form>"] = str_replace("name='play{$index}'", "name='play{$index}' style='background-color:rgba(100, 200, 50, 0.5); color:white;' ", $html["<form>"]);
                        }
                        $over = "Diagonal";
                        break;
                    }
                }

            }

            //$_SESSION["over] para evitar que el usuario siga jugando
            if (isset($over)) {
                $html["<turn>"] = "<h3>" . $lastPlayer->getName() . " won the game with a {$over} play! </h3><br />";
                /* Game Over */
                $_SESSION["over"] = true;
            } else {
                if (!in_array("0", $_SESSION["table"])) {
                    $html["<turn>"] = "<h3> Game Over! </h3>";
                    /* Game Over */
                    $_SESSION["over"] = true;
                }
            }

            echo implode("", array_values($html));

        }
        ?>
    </body>
</html>