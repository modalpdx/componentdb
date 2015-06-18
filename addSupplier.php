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

  $inName = grabPostAttr("suppName");  // REQUIRED - not NULL
  if($inName != NULL) {
    if(isUniqueName($inName, "Supplier", $db) == 0) {
        $inAddr1    = grabPostAttr("suppAddr1");
        $inAddr2    = grabPostAttr("suppAddr2");
        $inAddr3    = grabPostAttr("suppAddr3");
        $inLoc      = grabPostAttr("suppLoc");
        $inReg      = grabPostAttr("suppReg");
        $inPostCode = grabPostAttr("suppPostCode");
        $inWeb      = grabPostAttr("suppWeb");
        $inPhone    = grabPostAttr("suppPhone");
        $inCtry     = grabPostArrAttr("suppCtry", 0);
    } else {
      $_SESSION['errmsg'] = "ERROR: Supplier name must be unique.";
      $validated = FALSE;
    }
  } else {
    $_SESSION['errmsg'] = "ERROR: Supplier name must be specified.";
    $validated = FALSE;
  }
  
  // At this point, all values should be validated. If so, add to the DB.
  if ($validated === TRUE) {
      if (! ($stmt = $db->prepare ( "INSERT INTO Supplier (
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

  <h1>Add a Supplier</h1>
<?php if(isset($_SESSION['errmsg']) && ($_SESSION['errmsg'] != ""))
{
    echo "<p class=\"error\">{$_SESSION['errmsg']}</p>";
    $_SESSION['errmsg'] = "";
}
?>

  <form action="addSupplier.php" method="post" name="supplierForm">
    <p><label for="suppName">Name*:</label> <input type="text" name="suppName"></p>
    <p><label for="suppAddr1">Address 1:</label> <input type="text" name="suppAddr1"></p>
    <p><label for="suppAddr2">Address 2:</label> <input type="text" name="suppAddr2"></p>
    <p><label for="suppAddr3">Address 3:</label> <input type="text" name="suppAddr3"></p>
    <p><label for="suppLoc">Locality:</label> <input type="text" name="suppLoc"></p>
    <p><label for="suppReg">Region:</label> <input type="text" name="suppReg"></p>
    <p><label for="suppPostCode">Postal Code:</label> <input type="text" name="suppPostCode"></p>
    <p><label for="suppWeb">Web:</label> <input type="text" name="suppWeb"></p>
    <p><label for="suppPhone">Phone:</label> <input type="text" name="suppPhone"></p>
    <p><label for="suppCtry">Country:</label> <select name="suppCtry">
    <option selected value="">--Select from list--</option>
<?php

buildOptList("Country", $db);

?>
    </select> 
    <p><input type="submit" value="Add Supplier"</p>
  </form>

<?php 
// Add HTML footer content
//
require_once "includes/footer.php";

