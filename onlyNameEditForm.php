<?php

// Turn on all error reporting.
//
error_reporting ( E_ALL );
ini_set ( 'display_errors', 'On' );

// Start a new PHP session, or make active an existing session.
//
session_start();

// Collection of PHP functions
//
require_once "functions.php";

// Connect to the database.
//
$db = connectDB();

// We're passing the ID of the component via a query string. If one is
// offered, grab it.
//
if($_GET['c'] && ($_GET['c'] != NULL))
{
    $outId = $_GET['c'];
}

// If we received POST data, start working...
//
if ($_POST) {

  // Flag to indicate we can continue through the "add" process
  //
  $validated = TRUE;

  $inName = grabPostAttr($fieldName);        // REQUIRED - not NULL

  // We're editing, so we only check for a name (unique check isn't
  // needed)
  //
  if($inName == NULL) {
    $_SESSION['errmsg'] = "ERROR: {$tblName} name must be specified.";
    $validated = FALSE;
  }

  // Prepare a statement to update a row in the Component table
  //
  if ($validated === TRUE) {
      if (! ($stmt = $db->prepare ( "UPDATE {$tblName} SET
          name = ?
          WHERE ID=?" )
      )) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      // Bind parameters to be used in the prepared statement
      //
      if (! $stmt->bind_param ( 
          "si", 
          $inName, 
          $outId
      )) {
          echo "ERROR: Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
          $_SESSION['message'] = "error";
      }

      // Execute the prepared statement with the bound parameters
      //
      if (! $stmt->execute ()) {
          echo "ERROR: Execute failed: (" . $stmt->errno . ") " . $stmt->error;
          $_SESSION['message'] = "error";
      }

      $stmt->close ();

      // If we have reached this point, we have succeeded. Set the
      // appropriate session message and redirect to the component table.
      //
      $_SESSION['message'] = "success";
      $hdrStr = 'Location: ' . strtolower($tblName) . 'Tbl.php';
      header($hdrStr);
  }
  else 
  {
      // Tell the next page reload that we had a problem.
      //
      $_SESSION['message'] = "error";
  }

  // Grab everything we need to grab from $_POST before getting
  // rid of it. We'll need this if the form isn't validated and
  // we want to prefill it up on reload (so the user doesn't have
  // to re-enter all the fields).
  //
  $outName = $_POST[$fieldName];

}
else 
{
    // We're starting fresh. The "message" session variable will be used
    // to pass information to this script across page reloads.
    //
    $_SESSION['message'] = "new";
   
    // Grab component attributes from the database. Join the Measure,
    // Package, and Vendor tables 
    //    
    $queryStr = "SELECT name
                 FROM {$tblName}
                 WHERE ID=" . $outId;

    
    if (! ($result = $db->query($queryStr))) {
        echo "ERROR: Could not retrieve component: (" . $db->errno . ") " . $db->error;
        $_SESSION['message'] = "error";
    }
 
    $inRow = $result->fetch_array(MYSQLI_ASSOC);
    
    // Note: buildOptList() is written to accept an array of selected
    // values, not a string, so the selected Measure, Package, and Vendor
    // must be in an array. It's easiest to just handle that here.
    //
    $outName = $inRow['name'];

}

// Add HTML header content
//
require_once "includes/header.php";
?>

  <h1>Edit a <?php echo $tblName; ?></h1>
<?php if(isset($_SESSION['errmsg']) && ($_SESSION['errmsg'] != ""))
{
    echo "<p class=\"error\">{$_SESSION['errmsg']}</p>";
    $_SESSION['errmsg'] = "";
}
?>

  <form action="edit<?php echo $tblName; ?>.php?c=<?php echo $outId; ?>" method="post" name="<?php echo strtolower($tblName); ?>Form">
  <p><label for="<?php echo $fieldName; ?>">Name*:</label> <input type="text" name="<?php echo $fieldName; ?>" <?php showExistVal($fieldName, $outName); ?>></p>
  <p><input type="submit" value="Edit <?php echo $tblName; ?>"</p>
  </form>

<?php 
// Add HTML footer content
//
require_once "includes/footer.php";
