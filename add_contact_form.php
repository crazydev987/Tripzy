<!DOCTYPE html>
<html>
    <head>
        <title>Contact Manager - Add Contact</title>
        <link rel="stylesheet" type="text/css" href="css/main.css" />
    </head>
    <body>
        <?php include("header.php"); ?>

        <main>
            <h2>Add Contact</h2>

            <form action="add_contact.php" method="post" id="add_contact_form"
                enctype="multipart/form-data">

                <div id="data">

                    <label>First Name:</label>
                    <input type="text" name="first_name" /><br />

                    <label>Last Name:</label>
                    <input type="text" name="last_name" /><br />

                    <label>Email Address:</label>
                    <input type="text" name="email_address" /><br />

                    <label>Phone Number:</label>
                    <input type="text" name="phone_number" /><br />

                    <label>Status:</label>
                    <input type="radio" name="status" value="member" />Member<br />
                    <input type="radio" name="status" value="nonmember" />Non-Member<br />

                    <label>Birth Date:</label>
                    <input type="date" name="dob" /><br />

                    <label>Upload Image:</label>
                    <input type="file" name="file1" /><br />

                </div>

                <div id="buttons">

                    <label>&nbsp;</label>
                    <input type="submit" value="Save Contact" /><br />

                </div>

            </form>

            <p><a href="index.php">View Contact List</a></p>
            
        </main>

        <?php include("footer.php"); ?>
    </body>
</html>
