<?php

// Turn on all error reporting.
error_reporting ( E_ALL );
ini_set ( 'display_errors', 'On' );

// Read in common functions
//
require_once "functions.php";

// Start a new session, or activate an existing session
//
session_start();

// Connect to the database.
//
$db = connectDB();

if($_POST)
{
    if(isset($_POST['delete']) && ($_POST['delete'] != NULL))
    {
        deleteById($tblName, $_POST['delete'], $db);
    }
}
else 
{
  $_SESSION['message'] = "new";
}

// Add HTML header content
//
require_once "includes/header.php";

//
// GET THE ROWS FROM THE DB
//
$results = allIdName($tblName, $db);

// Start with NULLs for our form values
//
$outId     = NULL;
$outName   = NULL;
?>
      <form action="<?php echo strtolower($tblName); ?>Tbl.php" method="post" name="{$tblName}TableForm">
      <h1><?php echo $tblName; ?> Table</h1>
        <table border="0">
          <tbody>
            <tr>
              <th>Name</th>
              <th class="actions">Actions</th>
            </tr>
<?php
// Populate the table rows with component data.
//
while ( $row = $results->fetch_assoc() ) {
  $outId = $row['ID'];
  $outName = $row['name'];
  printf ( "\t<tr>\n" . 
      "\t\t<td>{$outName}</td>\n" . 
      "\t<td><a href=\"edit{$tblName}.php?c={$outId}\">Edit</a>\n" . 
      "\t<button type=\"submit\" name=\"delete\"" .  " value=\"{$outId}\">Delete</button></td>\n" . 
      "\t</tr>\n" );
}
?>
        </tbody>
        </table>
      </form>

    </div> <!-- end #content -->
<?php 
require_once "includes/footer.php";
