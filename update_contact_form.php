<?php
    require_once('database.php');
    // get the data from the form
    $contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);

    // select the contact from the database
    $query = 'SELECT * FROM contacts WHERE contactID = :contact_id';

        $statement = $db->prepare($query);
        $statement->bindValue(':contact_id', $contact_id);        

        $statement->execute();
        $contact = $statement->fetch();
        $statement->closeCursor();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Contact Manager - Update Contact</title>
        <link rel="stylesheet" type="text/css" href="css/main.css" />
    </head>
    <body>
        <?php include("header.php"); ?>

        <main>
            <h2>Update Contact</h2>

            <form action="update_contact.php" method="post" id="update_contact_form"
                enctype="multipart/form-data">

                <div id="data">

                    <input type="hidden" name="contact_id"
                        value="<?php echo $contact['contactID']; ?>" />

                    <label>First Name:</label>
                    <input type="text" name="first_name"
                        value="<?php echo $contact['firstName']; ?>" /><br />

                    <label>Last Name:</label>
                    <input type="text" name="last_name"
                        value="<?php echo $contact['lastName']; ?>" /><br />

                    <label>Email Address:</label>
                    <input type="text" name="email_address"
                        value="<?php echo $contact['emailAddress']; ?>" /><br />

                    <label>Phone Number:</label>
                    <input type="text" name="phone_number"
                        value="<?php echo $contact['phone']; ?>" /><br />

                    <label>Status:</label>
                    <input type="radio" name="status" value="member"
                        <?php echo ($contact['status'] == 'member') ? 'checked' : ''; ?> />Member<br />
                    <input type="radio" name="status" value="nonmember"
                        <?php echo ($contact['status'] == 'nonmember') ? 'checked' : ''; ?> />Non-Member<br />

                    <label>Birth Date:</label>
                    <input type="date" name="dob"
                        value="<?php echo $contact['dob']; ?>" /><br />

                </div>

                <div id="buttons">

                    <label>&nbsp;</label>
                    <input type="submit" value="Update Contact" /><br />

                </div>

            </form>

            <p><a href="index.php">View Contact List</a></p>
            
        </main>

        <?php include("footer.php"); ?>
    </body>
</html>
