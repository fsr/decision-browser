<?php
// redirect user if no search term was specified
if (!isset($_GET['query'])) {
    header('Location: index.php');
}

//include main config file
include("config.php");
?>

<html>
    <head>
        <title>Search | iFSR Decision Database</title>
    </head>

    <body>
        <header>
            <form action="search.php" method="get">
                <p><input type="text" name="query" value="<?php echo htmlspecialchars($_GET['query']); ?>" /></p>
                <input type="submit" />
            </form>
        </header>

        <hr />

        <?php
        // set up the database connection, add db if not existent
        // TODO: Remove this (put it into a separate file?)
        $db = new SQLite3($db_path) or die('Unable to open database');
        $query = "CREATE TABLE IF NOT EXISTS decisions (
                    decision_id STRING PRIMARY KEY,
                    text STRING,
                    comment STRING,
                    v_yes INTEGER,
                    v_no INTEGER,
                    v_neutral INTEGER,
                    date STRING,
                    link STRING,
                    accepted INTEGER)";
        $db->exec($query) or die('Create db failed');
        $db->close();

        // open a read-only connection to the database and receive all matching rows
        $db = new SQLite3($db_path, SQLITE3_OPEN_READONLY) or die('Unable to open database');
        // TODO: Use bindValue rather than the construct below.
        $smt = $db->prepare("SELECT * FROM decisions WHERE decision_id LIKE '%". $_GET['query'] ."%' OR text LIKE '%". $_GET['query'] ."%' OR comment LIKE '%". $_GET["query"] ."%' OR date LIKE '%". $_GET["query"] ."%' ORDER BY decision_id DESC");
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
                    <p class="heading"> Beschluss <?php print($row['decision_id']); ?></p>
                    <p class="outcome"><?php if ($row['accepted'] == 1) {print("Angenommen.");} else {print("Abgelehnt.");}?></p>
                    <p class="text"><?php print($row['text']); ?></p>
                    <p class="comment"><?php print($row['comment']); ?></p>
                    <p class="votes">[ <?php print($row['v_yes'] ." | ". $row['v_no'] ." | ". $row['v_neutral']); ?> ]</p>
                    <p class="date">Beschlossen am <?php if (strlen($row['link']) != 0) { print("<a href='". $row['link'] ."'>". $row['date'] ."</a>"); } else { print($row['date']); } ?>.
                </article>
                

            <?php
            }

            if (!$returned_something) {
            ?>

            <article>
                <p class="heading">
                    Uh oh! Seems like no decision matched your criteria.
                </p>
            </article>

            <?php
            }
            ?>

        </div>
    </body>
</html>
