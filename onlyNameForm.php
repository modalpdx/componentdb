<?php

// Turn on all error reporting.
//
error_reporting( E_ALL );
ini_set('display_errors', 'On');

// Start a new PHP session, or make active an existing session.
//
session_start();

// Collection of PHP functions
//
require_once "functions.php";

// Connect to the database.
//
$db = connectDB();

// If we received POST data, start working...
if ($_POST) {

  // Flag to indicate we can continue through the "add" process
  $validated = TRUE;

  $inName = grabPostAttr($fieldName);  // REQUIRED - not NULL

  if($inName != NULL) {
    if(isUniqueName($inName, $tblName, $db) != 0) {
      $_SESSION['errmsg'] = "ERROR: {$tblName} name must be unique.";
      $validated = FALSE;
    }
  } else {
    $_SESSION['errmsg'] = "ERROR: {$tblName} name must be specified.";
    $validated = FALSE;
  }
  
  // At this point, all values should be validated. If so, add to the DB.
  if ($validated === TRUE) {

      // Prepare a statement to insert rows in a user-specified table
      //
      if (! ($stmt = $db->prepare ( "INSERT INTO {$tblName} (
          name
      ) VALUES (?)" ))) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      // Bind parameters to be used in the prepared statement
      //
      if (! $stmt->bind_param ( 
          "s", 
          $inName 
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
      //header('Location: index.php');
      $hdrStr = 'Location: ' . strtolower($tblName) . 'Tbl.php';
      header($hdrStr);
  }
  else 
  {
      // Tell the next page reload that we had a problem.
      //
      $_SESSION['message'] = "error";
  }
}
else 
{
    // We're starting fresh. The "message" session variable will be used
    // to pass information to this script across page reloads.
    //
    $_SESSION['message'] = "new";
}

// Add HTML header content
//
require_once "includes/header.php";
?>
      <h1>Add a <?php echo $tblName; ?></h1>
<?php if(isset($_SESSION['errmsg']) && ($_SESSION['errmsg'] != ""))
{
    echo "<p class=\"error\">{$_SESSION['errmsg']}</p>";
    $_SESSION['errmsg'] = "";
}
?>

      <form action="add<?php echo $tblName; ?>.php" method="post" name="<?php echo $tblName; ?>Form">
        <p><label for="<?php echo $fieldName; ?>">Name*:</label> <input type="text" name="<?php echo $fieldName; ?>"></p>
        <p><input type="submit" value="Add <?php echo $tblName; ?>"</p>
      </form>

<?php 
// Add HTML footer content
//
require_once "includes/footer.php";

