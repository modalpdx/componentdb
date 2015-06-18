<?php

// Turn on all error reporting.
//
error_reporting ( E_ALL );
ini_set ( 'display_errors', 'On' );

// Collection of PHP functions
//
require_once "functions.php";

// Start a new PHP session, or make active an existing session.
//
session_start();

// Connect to the database.
//
$db = connectDB();

// If we received POST data, start working...
//
if ($_POST) {

  // Flag to indicate we can continue through the "add" process
  //
  $validated = TRUE;

  $inName = grabPostAttr("vendName");  // REQUIRED - not NULL
  if($inName != NULL) {
    if(isUniqueName($inName, "Vendor", $db) == 0) {
        $inAddr1    = grabPostAttr("vendAddr1");
        $inAddr2    = grabPostAttr("vendAddr2");
        $inAddr3    = grabPostAttr("vendAddr3");
        $inLoc      = grabPostAttr("vendLoc");
        $inReg      = grabPostAttr("vendReg");
        $inPostCode = grabPostAttr("vendPostCode");
        $inWeb      = grabPostAttr("vendWeb");
        $inPhone    = grabPostAttr("vendPhone");
        $inCtry     = grabPostArrAttr("vendCtry", 0);
    } else {
      $_SESSION['errmsg'] = "ERROR: Vendor name must be unique.";
      $validated = FALSE;
    }
  } else {
    $_SESSION['errmsg'] = "ERROR: Vendor name must be specified.";
    $validated = FALSE;
  }
  
  // At this point, all values should be validated. If so, add to the DB.
  if ($validated === TRUE) {
      if (! ($stmt = $db->prepare ( "INSERT INTO Vendor (
          name,
          address1,
          address2,
          address3,
          locality,
          region,
          postalcode,
          web,
          phone,
          CountryID
      ) VALUES (?,?,?,?,?,?,?,?,?,?)" ))) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
      }

      if (! $stmt->bind_param ( 
          "sssssssssi", 
          $inName, 
          $inAddr1, 
          $inAddr2, 
          $inAddr3, 
          $inLoc, 
          $inReg, 
          $inPostCode, 
          $inWeb, 
          $inPhone,
          $inCtry
      )) {
          echo "ERROR: Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      }

      if (! $stmt->execute ()) {
          echo "ERROR: Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      }

      $stmt->close ();
      // If we have reached this point, we have succeeded. Set the
      // appropriate session message and redirect to the component table.
      //
      $_SESSION['message'] = "success";
      header('Location: index.php');
  }
  else 
  {
      // Tell the next page reload that we had a problem.
      //
      $_SESSION['message'] = "error";
  }
}

// Add HTML header content
//
require_once "includes/header.php";
?>

  <h1>Add a Vendor</h1>
<?php if(isset($_SESSION['errmsg']) && ($_SESSION['errmsg'] != ""))
{
    echo "<p class=\"error\">{$_SESSION['errmsg']}</p>";
    $_SESSION['errmsg'] = "";
}
?>

  <form action="addVendor.php" method="post" name="vendorForm">
    <p><label for="vendName">Name*:</label> <input type="text" name="vendName"></p>
    <p><label for="vendAddr1">Address 1:</label> <input type="text" name="vendAddr1"></p>
    <p><label for="vendAddr2">Address 2:</label> <input type="text" name="vendAddr2"></p>
    <p><label for="vendAddr3">Address 3:</label> <input type="text" name="vendAddr3"></p>
    <p><label for="vendLoc">Locality:</label> <input type="text" name="vendLoc"></p>
    <p><label for="vendReg">Region:</label> <input type="text" name="vendReg"></p>
    <p><label for="vendPostCode">Postal Code:</label> <input type="text" name="vendPostCode"></p>
    <p><label for="vendWeb">Web:</label> <input type="text" name="vendWeb"></p>
    <p><label for="vendPhone">Phone:</label> <input type="text" name="vendPhone"></p>
    <p><label for="vendCtry">Country:</label> <select name="vendCtry">
    <option selected value="">--Select from list--</option>
<?php

buildOptList("Country", $db);

?>
    </select> 
    <p><input type="submit" value="Add Vendor"</p>
  </form>

<?php 
// Add HTML footer content
//
require_once "includes/footer.php";

