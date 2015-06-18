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

// Get the rows from the DB
//
$results = allVendSupp($tblName, $db);

// Start with NULLs for our form values
//
$outId   = NULL;
$outName = NULL;
$outAdd1 = NULL;
$outAdd2 = NULL;
$outAdd3 = NULL;
$outLoc  = NULL;
$outReg  = NULL;
$outPost = NULL;
$outWeb  = NULL;
$outPh   = NULL;
$outCtry = NULL;
?>
      <form action="<?php echo strtolower($tblName); ?>Tbl.php" method="post" name="<?php echo strtolower($tblName); ?>TableForm">
        <h1><?php echo $tblName; ?> Table</h1>
        <table border="0">
          <tbody>
            <tr>
              <th>Name</th>
              <th>Address</th>
              <th>Web</th>
              <th>Phone</th>
              <th class="actions">Actions</th>
            </tr>
<?php
// Populate the table rows with component data.
//
while ( $row = $results->fetch_assoc() ) {
  $outId   = $row['ID'];
  $outName = $row['name'];
  $outAdd1 = $row['address1'];
  $outAdd2 = $row['address2'];
  $outAdd3 = $row['address3'];
  $outLoc  = $row['locality'];
  $outReg  = $row['region'];
  $outPost = $row['postalcode'];
  $outWeb  = $row['web'];
  $outPh   = $row['phone'];
  $outCtry = $row['countryname'];
  printf ( "\t<tr>\n" . 
      "\t\t<td>{$outName}</td>\n" .
      "\t\t<td>");
  if($outAdd1 != NULL) {
      printf("{$outAdd1}<br>");
  }
  if($outAdd2 != NULL) {
      printf("{$outAdd2}<br>");
  }
  if($outAdd3 != NULL) {
      printf("{$outAdd3}<br>");
  }
  if($outLoc != NULL) {
      printf("{$outLoc}<br>");
  }
  if($outReg != NULL) {
      printf("{$outReg}<br>");
  }
  if($outCtry != NULL) {
      printf("{$outCtry}");
  }
  printf("</td>\n" .
      "\t\t<td><a target=\"_blank\" href=\"{$outWeb}\">{$outWeb}</a></td>\n" .
      "\t\t<td>{$outPh}</td>\n" . 
      "\t<td><a href=\"edit{$tblName}.php?c={$outId}\">Edit</a>\n" . 
      "\t<button type=\"submit\" name=\"delete\"" .  " value=\"{$outId}\">Delete</button></td>\n" . 
      "\t</tr>\n");
}
?>
        </tbody>
        </table>
      </form>

    </div> <!-- end #content -->
<?php 
require_once "includes/footer.php";
