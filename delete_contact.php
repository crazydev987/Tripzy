<?php
    require_once('database.php');
    // get the data from the form
    $contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);

    // code to delete contact from database
    // validate inputs
    if ($contact_id != false)
    {
        // delete the contact from the database
        $query = 'DELETE FROM contacts WHERE contactID = :contact_id';

        $statement = $db->prepare($query);
        $statement->bindValue(':contact_id', $contact_id);        

        $statement->execute();
        $statement->closeCursor();
    }

    // reload index page
    $url = "index.php";
    header("Location: " . $url);
    die();
?>