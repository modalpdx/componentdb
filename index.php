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
        deleteById("Component", $_POST['delete'], $db);
    }
}
else 
{
  $_SESSION['message'] = "new";
}

// Add HTML header content
//
require_once "includes/header.php";

$queryStr = "SELECT CM.id, CM.name, CM.pins, CM.partno, CM.value, CM.quantity, P.name, M.name
             FROM Component CM
             LEFT JOIN Package P ON P.ID = CM.PackageID
             LEFT JOIN Measure M ON M.ID = CM.MeasureID";

if (! ($stmt = $db->prepare ( $queryStr ))) {
  echo "Prepare failed: (" . $db->errno . ") " . $db->error;
  $_SESSION['message'] = "error";
}
if (! $stmt->execute ()) {
  echo "Execute failed: (" . $db->errno . ") " . $db->error;
  $_SESSION['message'] = "error";
}

// Start with NULLs for our form values
//
$outCMId     = NULL;
$outCMName   = NULL;
$outPins     = NULL;
$outPartno   = NULL;
$outValue    = NULL;
$outQuant    = NULL;
$outPkgName  = NULL;
$outMeasName = NULL;

if (! $stmt->bind_result ( $outCMId, $outCMName, $outPins, $outPartno, 
    $outValue, $outQuant, $outPkgName, $outMeasName )) {
  echo "Binding output result parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  $_SESSION['message'] = "error";
}
?>
      <form action="index.php" method="post" name="vidTableForm">
        <h1>Component Table</h1>
        <table border="0">
          <tbody>
            <tr>
              <th>Name</th>
              <th>Part No.</th>
              <th>Value</th>
              <th>Measure</th>
              <th>Pins</th>
              <th>Package</th>
              <th>Quantity</th>
              <th class="actions">Actions</th>
            </tr>
<?php
// Populate the table rows with component data.
//
while ( $stmt->fetch () ) {
  printf ( "\t<tr>\n" . 
      "\t\t<td>%s</td>\n" . 
      "\t\t<td>%s</td>\n" . 
      "\t\t<td>%s</td>\n" . 
      "\t\t<td>%s</td>\n" . 
      "\t\t<td>%d</td>\n" . 
      "\t\t<td>%s</td>\n" . 
      "\t\t<td>%d</td>\n" . 
      //"\t<td><button type=\"submit\" name=\"edit\"" .  " value=\"{$outCMId}\">Edit</button>\n" . 
      "\t<td><a href=\"editComponent.php?c={$outCMId}\">Edit</a>\n" . 
      "\t<button type=\"submit\" name=\"delete\"" .  " value=\"{$outCMId}\">Delete</button></td>\n" . 
      "\t</tr>\n", $outCMName, $outPartno, $outValue, $outMeasName, $outPins, $outPkgName, $outQuant );
}
$stmt->close ();
?>
        </tbody>
        </table>
      </form>

    </div> <!-- end #content -->
<?php 
require_once "includes/footer.php";
