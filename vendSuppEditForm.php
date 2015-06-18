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

// We're passing the ID of the item via a query string. If one is
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

  $inName = grabPostAttr("vendSuppName");        // REQUIRED - not NULL

  // We are editing, so only check that a name is entered (no unique check).
  //
  if($inName != NULL) {
      // With the required attributes in our hands, get the
      // remaining attributes.
      //
      $inAdd1 = grabPostAttr("vendSuppAdd1");
      $inAdd2 = grabPostAttr("vendSuppAdd2");
      $inAdd3 = grabPostAttr("vendSuppAdd3");
      $inLoc  = grabPostAttr("vendSuppLoc");
      $inReg  = grabPostAttr("vendSuppReg");
      $inPost = grabPostAttr("vendSuppPost");
      $inWeb  = grabPostAttr("vendSuppWeb");
      $inPh   = grabPostAttr("vendSuppPh");
      $inCtry = grabPostAttr("vendSuppCtry");
  } else {
    $_SESSION['errmsg'] = "ERROR: {$tblName} name must be specified.";
    $validated = FALSE;
  }

  // Prepare a statement to update a row in the vend/supp table
  //
  if ($validated === TRUE) {
      if (! ($stmt = $db->prepare ( "UPDATE {$tblName} SET
          name = ?,
          address1 = ?,
          address2 = ?,
          address3 = ?,
          locality = ?,
          region = ?,
          postalCode = ?,
          web = ?,
          phone = ?,
          CountryID = ?
          WHERE ID=?" )
      )) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      // Bind parameters to be used in the prepared statement
      //
      if (! $stmt->bind_param ( 
          "sssssssssii", 
          $inName, 
          $inAdd1, 
          $inAdd2, 
          $inAdd3, 
          $inLoc, 
          $inReg, 
          $inPost, 
          $inWeb, 
          $inPh,
          $inCtry,
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
      // appropriate session message and redirect to the vend/supp table.
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
  $outName     = $_POST['vendSuppName'];
  $outAdd1     = $_POST['vendSuppAdd1'];
  $outAdd2     = $_POST['vendSuppAdd2'];
  $outAdd3     = $_POST['vendSuppAdd3'];
  $outLoc      = $_POST['vendSuppLoc'];
  $outReg      = $_POST['vendSuppReg'];
  $outPost     = $_POST['vendSuppPost'];
  $outWeb      = $_POST['vendSuppWeb'];
  $outPh       = $_POST['vendSuppPh'];
  $outCtryID[] = $_POST['vendSuppCtry'];

}
else 
{
    // The "message" session variable will be used to pass information to
    // this script across page reloads.
    //
    $_SESSION['message'] = "new";
   
    // Grab vend/supp attributes from the database. 
    //    
    $queryStr = "SELECT A.name, A.address1, A.address2, A.address3, A.locality, A.region, A.postalcode, A.web, A.phone, A.CountryID
                 FROM {$tblName} A
                 WHERE ID={$outId}";
    
    if (! ($result = $db->query($queryStr))) {
        echo "ERROR: Could not retrieve " . strtolower($tblName) . ": (" . $db->errno . ") " . $db->error;
        $_SESSION['message'] = "error";
    }
 
    $inRow = $result->fetch_array(MYSQLI_ASSOC);
    
    // Note: buildOptList() is written to accept an array of selected
    // values, not a string, so the selected Measure, Package, and Vendor
    // must be in an array. It's easiest to just handle that here.
    //
    $outName     = $inRow['name'];
    $outAdd1     = $inRow['address1'];
    $outAdd2     = $inRow['address2'];
    $outAdd3     = $inRow['address3'];
    $outLoc      = $inRow['locality'];
    $outReg      = $inRow['region'];
    $outPost     = $inRow['postalcode'];
    $outWeb      = $inRow['web'];
    $outPh       = $inRow['phone'];
    $outCtryID[] = $inRow['CountryID'];
    //$outCtryName = $inRow['countryname'];


    // Grab countries
    //
    $queryStr = "SELECT ID, name FROM Country";
    
    if (! ($result = $db->query($queryStr))) {
        echo "ERROR: Could not retrieve countries: (" . $db->errno . ") " . $db->error;
        $_SESSION['message'] = "error";
    }
    
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
  <p><label for="vendSuppName">Name*:</label> <input type="text" name="vendSuppName" <?php showExistVal("vendSuppName", $outName); ?>></p>
  <p><label for="vendSuppAdd1">Address 1:</label> <input type="text" name="vendSuppAdd1" value="<?php echo $outAdd1; ?>"></p>
  <p><label for="vendSuppAdd2">Address 2:</label> <input type="text" name="vendSuppAdd2" value="<?php echo $outAdd2; ?>"></p>
  <p><label for="vendSuppAdd3">Address 3:</label> <input type="text" name="vendSuppAdd3" value="<?php echo $outAdd3; ?>"></p>
  <p><label for="vendSuppLoc">Locality:</label> <input type="text" name="vendSuppLoc" value="<?php echo $outLoc; ?>"></p>
  <p><label for="vendSuppReg">Region:</label> <input type="text" name="vendSuppReg" value="<?php echo $outReg; ?>"></p>
  <p><label for="vendSuppPost">Postal Code:</label> <input type="text" name="vendSuppPost" value="<?php echo $outPost; ?>"></p>
  <p><label for="vendSuppWeb">Web:</label> <input type="text" name="vendSuppWeb" value="<?php echo $outWeb; ?>"></p>
  <p><label for="vendSuppPh">Phone:</label> <input type="text" name="vendSuppPh" value="<?php echo $outPh; ?>"></p>
  <p><label for="vendSuppCtry">Country:</label> <select name="vendSuppCtry">
  <option value="">--Select from list--</option>
<?php

buildOptList("Country", $db, $outCtryID);

?>
    </select> 
    <p><input type="submit" value="Edit <?php echo $tblName; ?>"</p>
  </form>

<?php 
// Add HTML footer content
//
require_once "includes/footer.php";
