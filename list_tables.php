<?php
$db = mysqli_connect('localhost', 'root', '', 'mj_chatbot');
$res = mysqli_query($db, 'SHOW TABLES');
while($row = mysqli_fetch_row($res)) {
    echo $row[0] . PHP_EOL;
}
mysqli_close($db);
