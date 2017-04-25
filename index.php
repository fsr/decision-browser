<?php
include("config.php");
?>

<html>
    <head>
        <title>iFSR Decision Database</title>
    </head>

    <body>
        <header>
            <form action="search.php" method="get">
                <p><input type="text" name="query" value="<?php echo htmlspecialchars($_GET['query']); ?>" /></p>
                <input type="submit" />
            </form>
        </header>

        <hr />
    </body>
</html>
