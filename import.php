<?php
include("config.php");


// open the import path
$dir = dir($import_path);

if ($dir == NULL || $dir == FALSE) {
    die("Could not open import directory!");
}

// Open the database
$db = new SQLite3($db_path) or die('Unable to open database');

// inserting is always the same statement
$stmt = $db->prepare("INSERT INTO decisions VALUES (:decision_id, :text, :rationale, :money_limit, :comment, :v_yes, :v_no, :v_neutral, :accepted, :date, :link)");

// global error variable for status report
$global_err = false;

// iterate through all entries
while (false !== ($entry = $dir->read())) {

    $fileinfo = pathinfo($entry);

    // only continue for yaml files
    if ($fileinfo['extension'] == "yaml") {

        // construct file path
        $file_path = $import_path . "/" . $entry;
        echo "Parsing ". $file_path ."...\n";

        $parsed = yaml_parse_file($file_path);
        if (!$parsed) {
            echo "Parsing file ". $file_path ." did not succeed. Please inspect the file and make sure it is valid YAML.\n";
        } else {
            // construct the necessary data for the decision entries
            $proto_date = $fileinfo['filename'];
            list($day, $month, $year) = explode(".", $proto_date);
            $proto_link = "https://ftp.ifsr.de/protokolle/". $year ."/". $year ."-". $month ."-". $day .".pdf";

            $failed = array();

            foreach ($parsed as $entry) {
                // for each entry, generate a SQL statement containing the required variables

                $stmt->reset();
                $stmt->clear();

                if (isset($entry['decision_id'])) {
                    $stmt->bindParam(':decision_id', $entry['decision_id'], SQLITE3_TEXT);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'decision_id' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['text'])) {
                    $stmt->bindParam(':text', $entry['text'], SQLITE3_TEXT);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'text' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['rationale'])) {
                    $stmt->bindParam(':rationale', $entry['rationale'], SQLITE3_TEXT);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'rationale' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['money_limit']) || array_key_exists('money_limit', $entry)) {
                    $stmt->bindParam(':money_limit', $entry['money_limit'], SQLITE3_FLOAT);
                } else {
                    echo "Encountered invalid decision entry with missing 'money_limit' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['comment'])) {
                    $stmt->bindParam(':comment', $entry['comment'], SQLITE3_TEXT);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'comment' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['v_yes'])) {
                    $stmt->bindParam(':v_yes', $entry['v_yes'], SQLITE3_INTEGER);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'v_yes' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['v_no'])) {
                    $stmt->bindParam(':v_no', $entry['v_no'], SQLITE3_INTEGER);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'v_no' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['v_neutral'])) {
                    $stmt->bindParam(':v_neutral', $entry['v_neutral'], SQLITE3_INTEGER);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'v_neutral' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                if (isset($entry['accepted'])) {
                    $stmt->bindParam(':accepted', $entry['accepted'], SQLITE3_INTEGER);
                } else {
                    echo "Encountered invalid decision entry with missing or empty 'accepted' field.\n";
                    array_push($failed, $entry);
                    continue;
                }

                $stmt->bindParam(':date', $proto_date, SQLITE3_TEXT);
                $stmt->bindParam(':link', $proto_link, SQLITE3_TEXT);
                
                // Showtime!
                if (!$stmt->execute()) {
                    echo "Failed to store a decision in the database!\n";
                    array_push($failed, $entry);
                }
            }

            // unlink the file

            // if there were any failed inserts, add them to the file again
            if (!empty($failed)) {
                $fh = fopen($file_path, 'w');
                fwrite($fh, yaml_emit($failed));
                fclose($fh);
                $global_err = true;
            } else {
                if (!unlink($file_path)) {
                    echo "Failed to unlink ". $file_path .". Please do so manually before re-running the script!\n";
                }
            }
        }
    }
}

$db->close();

if ($global_err) {
    echo "Failed to insert one or more entries into the database. The corresponding YAML artifacts have been retained in the folder. Please fix any errors in these files.\n";
}

?>
