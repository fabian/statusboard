<?php

$location = isset($_GET['location']) ? $_GET['location'] : 'Brisbane';
$timezone = isset($_GET['timezone']) ? $_GET['timezone'] : 'Australia/Brisbane';

$timezone = new DateTimeZone($timezone);
$offset = $timezone->getOffset(new DateTime('now'));

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
        <meta http-equiv="Cache-control" content="no-cache" />

        <meta application-name="Time <?php echo htmlentities($location); ?>" data-allows-resizing="NO" data-default-size="4,4" data-allows-scrolling="NO" />

        <style type="text/css">
            * { 
                margin: 0;
                padding: 0;
            }

            body {
                color: rgb(174, 183, 188);
                font-family: 'Roadgeek 2005 Series C', sans-serif;
                font-size: 20px;
                line-height: 24px;
            }

            .container {
                width: 250px;
                height: 250px;
                text-align: center;
            }

            h1 {
                font-size: 105px;
                line-height: 120px;
                margin-top: 35px;
                margin-bottom: 28px;
                font-weight: normal;
                background: -webkit-linear-gradient(#ffffff, #aeb7bc);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            h2 {
                color: #555;
                text-transform: uppercase;
                font-size: 30px;
                margin-top: 30px;
                line-height: 18px;
                font-weight: normal;
            }
        </style>

        <script type="text/javascript">
            function init()
            {
                setInterval(function () {
                    var d = new Date();
                    var offset = d.getTimezoneOffset() * 60 * 1000 + <?php echo htmlentities($offset); ?> * 1000;
                    d = new Date(d.getTime() + offset);
                    var hours = d.getHours();
                    if (hours < 10) {
                        hours = '0' + hours;
                    }
                    var minutes = d.getMinutes();
                    if (minutes < 10) {
                        minutes = '0' + minutes;
                    }
                    document.getElementById('time').innerHTML = hours + ':' + minutes;
                }, 100);
            }
        </script>
    </head>

    <body onload="init()">
        <div class="container">
            <h2><?php echo htmlentities($location); ?></h2>
            <h1 id="time"></h1>
        </div>
    </body>
</html>
