<?php
header('Connection: close');
    session_start();
    $_SESSION['logged'] = TRUE;
?>
<HTML>
    <BODY>
        <a href="streamer.php?vid=test.mp4">refresh</a>
        <CENTER>
            <VIDEO HEIGHT='600'  SRC='streamer.php?vid=test.mp4' CONTROLS='true'  >
            </VIDEO>;
        </CENTER>
    </BODY>
</HTML>