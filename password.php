<?php
/**
 * Copyright (C) 2013 peredur.net
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
include_once 'inc/register.inc.php';
include_once 'inc/functions.php';
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon" />
    <title>Secure Login: Password Change Form</title>
    <script type="text/JavaScript" src="js/sha512.js"></script> 
    <script type="text/JavaScript" src="js/forms.js"></script>
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/default.css" />
  </head>
  <body>
    <div id="container">
      <div id="noappcontent">
        <?php
        if (!empty($error_msg)) {
            echo $error_msg;
        }
        ?>
        <form method="post" id="passwordChangeForm" name="password_change_form" action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>">
          <input type="text" name="email" id="email" placeholder="Email address" /><br>
          <input type="password" name="oldpassword" id="oldpassword" placeholder="Previous Password"/><br>
          <input type="password" name="password" id="password" placeholder="Password, min 6 chars - must include A-Z a-z 0-9"/><br>
          <input type="password" name="confirmpwd" id="confirmpwd" placeholder="Confirm Password" /><br>
          <input type="button" value="Register" onclick="return regformhash(this.form, this.form.username, this.form.email, this.form.password, this.form.confirmpwd);" /> 
          <p>Return to the <a href="index.php">login page</a>.</p>
        </form>
      </div>
    </div>
  </body>
</html>
