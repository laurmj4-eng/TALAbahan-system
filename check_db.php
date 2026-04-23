<?php
$db = mysqli_connect('localhost', 'root', '', 'mj_chatbot');
if (!$db) die('Connection failed');
$res = mysqli_query($db, 'DESCRIBE orders');
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
mysqli_close($db);
