<?php

include("config.php");

// set up the database connection, add db if not existent
$db = new SQLite3($db_path) or die('Unable to open database');
$query = "CREATE TABLE IF NOT EXISTS decisions (
            decision_id STRING PRIMARY KEY,
            text TEXT,
            rationale TEXT,
            money_limit REAL,
            comment TEXT,
            v_yes INTEGER,
            v_no INTEGER,
            v_neutral INTEGER,
            accepted INTEGER,
            date TEXT,
            link TEXT)";
$db->exec($query) or die('Create db failed');

if (isset($_GET['dummydata'])) {
    $db->exec("INSERT INTO decisions VALUES ( '2017/02', 'Der FSR Informatik beschließt hiermit, getestete Software abzuschaffen.', 'Muss halt.', NULL, 'Geistreicher Kommentar', 13, 2, 1, 1, '20.12.2017', 'https://ifsr.de' )");
    $db->exec("INSERT INTO decisions VALUES ( '2017/03', 'Der FSR Informatik beschließt hiermit, ausschließlich ungetestete Hardware anzuschaffen.', '', NULL, 'Dummer Kommentar', 8, 7, 2, 0, '24.12.2017', 'https://ifsr.de' )");
    $db->exec("INSERT INTO decisions VALUES ( '2017/01', 'Matthias beantragt einen Finanzrahmen über 200,12€ zur Deckung seiner Hosting-Kosten.', '', 200.12, 'Keine Gegenrede.', 17, 0, 0, 1, '01.04.2017', 'https://github.com/fsr/matthias' )");
    // Mehrheitsbeschlüsse werden mit [0 | 0 | 0], aber `accepted` = 1 gekennzeichnet.
    $db->exec("INSERT INTO decisions VALUES ( '2017/04', 'Der FSR Informatik beschließt hiermit, mehrheitlich zu beschließen.', 'Ist halt cool.', NULL, '', 0, 0, 0, 1, '30.12.2017', 'http://stura.link/rektor' )");          
}

$db->close();

header('Location: index.php');

?>
