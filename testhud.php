<html>

<head>
    <title>PHP Bot Test Hud</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        textarea {
            background-color: white;
            color: black;
            padding: .5rem;
            display: block;
            margin: auto;
        }

        .ui-widget {
            width: 543px;
            /* margin: 1.5rem 0; */
            padding: 1rem;
            /* display: block; */
            list-style-type: none;
            margin-block-end: 0px;
            margin-block-start: 0px;
            text-align: left;
        }

        .ui-widget,
        .ui-widget * {
            background-color: rgb(46, 46, 46);
        }

        .ui-helper-hidden-accessible {
            display: none;
        }

        li.ui-menu-item {
            margin: 1.5rem 0;
        }
    </style>
    <link type="text/css" media="screen" href="style.css" rel="stylesheet">
    <?php
    // This is where you put your config information
    // Set this to the name of the group.
    $GROUP = 'My Group';
    // Set this to the group password.
    $PASSWORD = 'password';
    // Set this to Corrade's HTTP Server URL.
    $botURL = 'http://127.0.0.1:8080/';
    $callbackURL = 'http://127.0.0.1:80/';

    require_once('functions.php');

    ?>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        $(document).ready(function() {

            var input = $("#formtextarea");
            var len = input.val().length;
            input[0].focus();
            input[0].setSelectionRange(len, len);

        });
        $(function() {
            var availableTags = [
                // Enter commands here to autofill
                "command=attach&attachments=slot,path",
                "command=agentaccess&action=set&access=M",
                "command=detach&type=path&attachments='path'",
                "command=fly&action=start",
                "command=getattachments",
                "command=tell&entity=&message=",
                "command=trashitem&item='path'",
                "command=logout",
                "command=terminate"
            ];
            $("#formtextarea").autocomplete({
                source: availableTags
            });
        });
    </script>

</head>

<body>
    <h1><a href="">Bot Test Hud</a></h1>
    <p>This ia a relatively simple form that uses PHP to send a <a target="_blank" href="https://grimore.org/secondlife/scripted_agents/corrade/api/commands">command to a Corrade bot</a>.</p>
    <p><b>Syntax:</b> command=sit&item=chair<br />
        <small><em>URL, group, password are defined in the PHP code and
                are added automatically.</em></small></p>
    <!-- This is the form -->
    <form id="botcommand" method="post">
        <textarea name="botcommand" id="formtextarea" autofocus rows="5" cols="80">command=</textarea>
        <button type="submit" name="submitTestHUD">Submit</button>
    </form>
    <!-- This is the new PHP. -->
    <?php

    function sendthecommand($botURL, $params)
    {

        ###########################################################################
        ##  Copyright (C) Wizardry and Steamworks 2016 - License: GNU GPLv3      ##
        ###########################################################################

        ###########################################################################
        ##                               INTERNALS                               ##
        ###########################################################################

        ####
        # II. Escape the data to be sent to Corrade.

        array_walk(
            $params,
            function (&$value, $key) {
                $value = rawurlencode($key) . "=" . rawurlencode($value);
            }
        );
        $postvars = implode('&', $params);

        ####
        # III. Use curl to send the message.
        if (!($curl = curl_init())) {
            print 0;
            return;
        }
        curl_setopt($curl, CURLOPT_URL, $botURL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postvars);
        curl_setopt($curl, CURLOPT_ENCODING, true);
        $result = curl_exec($curl);
        curl_close($curl);

        ####
        # IV. Grab the status of the command.
        $status = urldecode(
            wasKeyValueGet(
                "success",
                $result
            )
        );

        ####
        # IV. Check the status of the command.
        switch ($status) {
            case "True":
                $followup = wasCSVToArray(
                    urldecode(
                        wasKeyValueGet(
                            "data",
                            $result
                        )
                    )
                );

                if ($followup[0] != "data") {

                    $responsetable = "<textarea rows='10' cols='80'>";
                    foreach ($followup as $key => $row) {
                        $responsetable .= $key . ' - ' . $row . "\n";
                    }
                    $responsetable .= "</textarea>";

                    echo '<div id="notificationBarBottomFixed"><span>Response:</span>' . $responsetable . '</div>';
                } else {
                    echo '<div id="notificationBarBottom">Command sent!</div>';
                }
                break;
            default:
                echo '<div id="notificationBarBottom">Corrade returned the error: ' . urldecode(
                    wasKeyValueGet(
                        "error",
                        $result
                    )
                ) . "</div>";
                break;
        }
    }

    if (isset($_POST['submitTestHUD'])) {
        $HUDcommand = $_POST['botcommand'];
        parse_str($HUDcommand, $HUDCommand);

        $credentials = array(
            'group' => $GROUP,
            'password' => $PASSWORD
        );
        $params = $credentials + $HUDCommand;
        sendthecommand($botURL, $params);
    }
    ?>


</body>

</html>