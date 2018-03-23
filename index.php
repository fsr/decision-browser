<?php
include("config.php");
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <title>iFSR Decision Database</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="https://fonts.googleapis.com/css?family=Fira+Sans:300,700" rel="stylesheet">
        <link href="style.css" rel="stylesheet" />
    </head>

    <body>
        <header>
            <h1><a href="index.php"><img src="logo_header.png" alt="iFSR Decision Database Browser" title="iFSR Decision Database Browser" /></a> Decision Database Browser</h1>
            <form action="search.php" method="get">
                <input type="text" name="query" placeholder="Suche" />
            </form>
        </header>

        <div class="content">
            <h2>Die neusten Beschlüsse</h2>
            <?php
            // open a read-only connection to the database and receive all matching rows
            $db = new SQLite3($db_path, SQLITE3_OPEN_READONLY) or die('Unable to open database');
            $smt = $db->prepare("SELECT * FROM decisions ORDER BY decision_id DESC LIMIT 5");
            $result = $smt->execute();
            ?>

            <div class="decisionlist">
                <?php
                $returned_something = false;
                while ($row = $result->fetchArray(1)) {
                    $returned_something = true;
                    // TODO: Add Link to the Detail View, shorten the text, add "more" link.
                    ?>
                    <article>
                        <div class="heading"><h3><?php 
                        if ($row['money_limit'] != NULL) { 
                            print("Finanzrahmen ". $row['decision_id'] ." (". $row['money_limit'] ."€)"); 
                        } else {
                            print("Beschluss ". $row['decision_id']);
                        } ?></h3>
                        <?php if ($row['accepted'] == 1) {print('<div class="outcome decided">Angenommen</div>');} else {print('<div class="outcome rejected">Abgelehnt</div>');}?></div>
                        <p class="date">Beschlossen am <?php if (strlen($row['link']) != 0) { print("<a href='". $row['link'] ."'>". $row['date'] ."</a>"); } else { print($row['date']); } ?>.</p>
                        <p class="text"><?php print($row['text']); ?></p>
                        <?php if (strlen($row['rationale']) != 0) {print('<p class="rationale"><b>Begründung:</b> '. $row['rationale'] .'</p>');} ?>
                        <div class="meta">
                            <p class="comment"><?php print($row['comment']); ?></p>
                            <?php if ($row['accepted'] == 1 && $row['v_yes'] == 0) { ?>
                                <p class="votes" title="Der Beschluss wurde mehrheitlich gefasst.">[ <span class="decided">Mehrheit</span> ]</p>
                            <?php } else { ?>
                                <p class="votes" title="Stimmen: [dafür | dagegen | Enthaltungen]">[ <?php print('<span class="decided">'. $row['v_yes'] .'</span> | <span class="rejected">'. $row['v_no'] ."</span> | ". $row['v_neutral']); ?> ]</p>
                            <?php } ?>
                        </div>
                    </article>
                    

                <?php
                }

                if (!$returned_something) {
                ?>

                <article>
                    <h2>Keine Ergebnisse</h2>
                    <p class="heading">
                        Es wurden keine Beschlüsse gefunden. :(
                    </p>
                </article>

                <?php
                }
                ?>

            </div>
        </div>

        <footer>
            Diese Datenbank ist ein Projekt des <a href="https://www.ifsr.de">FSR Informatik</a>. – <a href="https://www.ifsr.de/fsr:kontakt">Impressum</a> – Dieses Projekt ist auf <a href="https://github.com/fsr/decision-browser">GitHub</a>.
        </footer>
    </body>
</html>
