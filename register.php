<?php
session_start(); //Start the session

$con = mysqli_connect('localhost', 'root', '', 'social');

if (mysqli_connect_errno()) {
    echo "Failed to connect: " . mysqli_connect_errno();
}

// Declaring variables to prevent errors
$fname = ""; //First name
$fname = ""; //Last name
$em = ""; //email
$em2 = ""; //email2
$password = ""; //password
$password2 = ""; //password2 
$date = ""; //Sign up date
$error_array = array(); //Holds error messages

if (isset($_POST['register_button'])) {

    // Registration form values

    //First name
    $fname = strip_tags($_POST['reg_fname']); //Strip tags removes entered HTML
    $fname = str_replace(' ', '', $fname); //Remove spaces
    $fname = ucfirst(strtolower($fname)); //Capitalize first letter, lower other letters
    $_SESSION['reg_fname'] = $fname; //Stores first name into session variable
    
    //Last name
    $lname = strip_tags($_POST['reg_lname']); //Strip tags removes entered HTML
    $lname = str_replace(' ', '', $lname); //Remove spaces
    $lname = ucfirst(strtolower($lname)); //Capitalize first letter, lower other letters
    $_SESSION['reg_lname'] = $lname; //Stores last name into session variable

    
    //Email
    $em = strip_tags($_POST['reg_email']); //Strip tags removes entered HTML
    $em = str_replace(' ', '', $em); //Remove spaces
    $em = ucfirst(strtolower($em)); //Capitalize first letter, lower other letters
    $_SESSION['reg_email'] = $em; //Stores email into session variable

    
    //Email 2
    $em2 = strip_tags($_POST['reg_email2']); //Strip tags removes entered HTML
    $em2 = str_replace(' ', '', $em2); //Remove spaces
    $em2 = ucfirst(strtolower($em2)); //Capitalize first letter, lower other letters
    $_SESSION['reg_email2'] = $em2; //Stores email into session variable
    
    //Password
    $password = strip_tags($_POST['reg_password']); //Strip tags removes entered HTML
  
    //Password 2
    $password2 = strip_tags($_POST['reg_password2']); //Strip tags removes entered HTML
  
    $date = date("Y-m-d"); //Current date

    if ($em == $em2 ) {
        if (filter_var($em, FILTER_VALIDATE_EMAIL)) { //Check if email is correct format
            $em = filter_var($em, FILTER_VALIDATE_EMAIL); //Email = validated version of email!
            
            //Check if email already exists!
            $e_check = mysqli_query($con, "SELECT email FROM users WHERE email='$em'");
            
            //Count the number of rows returned
            $num_rows = mysqli_num_rows($e_check);

            if ($num_rows > 0) {
                array_push($error_array, "Email already in use<br>"); 
            }

        } else {
            array_push($error_array, "Invalid email format<br>");
        }
    } else {
        array_push($error_array, "Emails don't match<br>");
    }
    //Check first name length (25 varchar in db users table)
    if (strlen($fname) > 25 || strlen($fname) < 2) {
        array_push($error_array, "Your first name must be between 2 and 25 characters<br>");
    }
    //Check last name length (25 varchar in db users table)
    if (strlen($lname) > 25 || strlen($lname) < 2) {
        array_push($error_array, "Your last name must be between 2 and 25 characters<br>");
    }
    //Check if pw-s match
    if ($password != $password2) {
        array_push($error_array, "Your passwords do not match!<br>");
    } else {
        if (preg_match('/[^A-Za-z0-9]/', $password)) { //Only allow english characters in pw
            array_push($error_array, "Your password can only contain english characters or numbers<br>");
        }
    }

    if (strlen($password < 30 || strlen($password) < 5)) {
        array_push($error_array, "Your pw must be between 5 and 30 characters<br>");
    }

    if (empty($error_array)) {
        $password = md5($password); //Encrypt the pw before sending to db
        
        //Generate username by concatenating first name and last name
        $username = strtolower($fname . "_" . $lname);
        //Check if anyone already has this username!
        $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");

        $i = 0; 
        //If username exists add number to username
        while(mysqli_num_rows($check_username_query) != 0) {
            $i++;
            $username = $username . "_" . $i;
            $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
        } 

        //Profile picture assignment 
        $rand = rand(1, 2); //Random nr between 1 and 2

        if ($rand == 1) 
            $profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
        else if ($rand == 2) 
            $profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";

        //Insert everything into database!!!
        $query = mysqli_query($con, "INSERT INTO users VALUES ('', '$fname','$lname','$username','$em','$password','$date', '$profile_pic', '0', '0', 'no', ',')");

        array_push($error_array, "<span style='color: #14C800;'>You're all set! Go ahead and login!</span><br>");

        //Clear session variables
        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_email2'] = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
</head>

<body>
    <!-- form action shows which file handles the data from the form -->
    <form action="register.php" method="POST">
        <!-- First name -->
        <input type="text" name="reg_fname" placeholder="First Name" value="<?php 
            if(isset($_SESSION['reg_fname'])) {
                echo $_SESSION['reg_fname'];
            } ?>" required>
        <br>
        <?php if (in_array("Your first name must be between 2 and 25 characters<br>", $error_array)) echo "Your first name must be between 2 and 25 characters<br>"; ?>

        <!-- Last name -->
        <input type="text" name="reg_lname" placeholder="Last Name" value="<?php 
            if(isset($_SESSION['reg_lname'])) {
                echo $_SESSION['reg_lname'];
            } ?>" required>
        <br>
        <?php if (in_array("Your last name must be between 2 and 25 characters<br>", $error_array)) echo "Your last name must be between 2 and 25 characters<br>"; ?>

        <!-- Email -->
        <input type="email" name="reg_email" placeholder="Email" value="<?php 
            if(isset($_SESSION['reg_email'])) {
                echo $_SESSION['reg_email'];
            } ?>" required>
        <br>
            
        <!-- Email confirm -->
        <input type="email" name="reg_email2" placeholder="Confirm Email" value="<?php 
            if(isset($_SESSION['reg_email2'])) {
                echo $_SESSION['reg_email2'];
            } ?>" required>
        <br>
        <?php if (in_array("Email already in use<br>", $error_array)) echo "Email already in use<br>"; 
            else if (in_array("Invalid email format<br>", $error_array)) echo "Invalid email format<br>"; 
            else if (in_array("Emails don't match<br>", $error_array)) echo "Emails don't match<br>"; ?>

        <!-- Password -->
        <input type="password" name="reg_password" placeholder="Password" required>
        <br>
        
        <!-- Password Confirm -->
        <input type="password" name="reg_password2" placeholder="Confirm Password" required>
        <br>
        <?php if (in_array("Your passwords do not match!<br>", $error_array)) echo "Your passwords do not match!<br>"; 
            else if (in_array("Your password can only contain english characters or numbers<br>", $error_array)) echo "Your password can only contain english characters or numbers<br>"; 
            else if (in_array("Your pw must be between 5 and 30 characters<br>", $error_array)) echo "Your pw must be between 5 and 30 characters<br>"; ?>    

        <!-- Submit button -->
        <input type="submit" name="register_button" value="Register">
        <br>
        <?php if (in_array("<span style='color: #14C800;'>You're all set! Go ahead and login!</span><br>", $error_array)) echo "<span style='color: #14C800;'>You're all set! Go ahead and login!</span><br>"; ?>

    </form>
</body>

</html>