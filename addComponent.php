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

// If we received POST data, start working...
//
if ($_POST) {

  // Flag to indicate we can continue through the "add" process
  //
  $validated = TRUE;

  $inName = grabPostAttr("compName");        // REQUIRED - not NULL
  if($inName != NULL) {
      $inQuant = grabPostAttr("compQuant");  // REQUIRED - not NULL
      if($inQuant != NULL) {
          // With the required attributes in our hands, get the
          // remaining attributes.
          //
          $inPartno = grabPostAttr("compPartno");
          $inVal    = grabPostAttr("compVal");
          $inPins   = grabPostAttr("compPins");
          $inMeas   = grabPostAttr("compMeas");
          $inPack   = grabPostAttr("compPkg");
          $inVend   = grabPostAttr("compVend");
      } else {
          //echo "<p>ERROR: Component quantity must be specified.</p>\n";
          $_SESSION['errmsg'] = "ERROR: Component quantity must be specified.";
          $validated = FALSE;
      }
  } else {
    //echo "<p>ERROR: Component name must be specified.</p>\n";
    $_SESSION['errmsg'] = "ERROR: Component name must be specified.";
    $validated = FALSE;
  }

  // Prepare a statement to insert a row into the Component table
  //
  if ($validated === TRUE) {
      if (! ($stmt = $db->prepare ( "INSERT INTO Component (
          name,
          pins,
          partno,
          value,
          quantity,
          MeasureID,
          PackageID,
          VendorID
      ) VALUES (?,?,?,?,?,?,?,?)" ))) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      // Bind parameters to be used in the prepared statement
      //
      if (! $stmt->bind_param ( 
          "sissiiii", 
          $inName, 
          $inPins, 
          $inPartno, 
          $inVal, 
          $inQuant, 
          $inMeas, 
          $inPack,
          $inVend
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

      // Grab the ID of the component that was just added.
      //
      $lastId = $db->insert_id;

      // Prepare a statement to insert a row into the Component_Category table
      //
      if (! ($stmt = $db->prepare ( "INSERT INTO Component_Category (
          compId,
          catId
      ) VALUES (?,?)" ))) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      //
      // Walk through the list of categories from the form (if any) and
      // add them to the Component_Category table.
      //
      
      if(isset($_POST['compCat']))
      {
          foreach($_POST['compCat'] as $cat)
          {
              // Bind parameters to be used in the prepared statement
              //
              if (! $stmt->bind_param ( 
                  "ii", 
                  $lastId, 
                  $cat
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
          }
      }


      // Execute the prepared statement with the bound parameters
      //
      if (! ($stmt = $db->prepare ( "INSERT INTO Supplier_Component (
          compId,
          supId
      ) VALUES (?,?)" ))) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      //
      // Walk through the list of suppliers from the form (if any) and
      // add them to the Supplier_Component table.
      //
      
      if(isset($_POST['compSupp']))
      {
          foreach($_POST['compSupp'] as $supp)
          {
              // Bind parameters to be used in the prepared statement
              //
              if (! $stmt->bind_param ( 
                  "ii", 
                  $lastId, 
                  $supp
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
          }
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
else 
{
    // We're starting fresh. The "message" session variable will be used
    // to pass information to this script across page reloads.
    //
    $_SESSION['message'] = "new";
}

// If component categories and/or suppliers were passed to this 
// script, prepare them for inclusion in the buildOptList() process.
//
$catList  = (isset($_POST['compCat'])  ? $_POST['compCat'] : NULL);
$supList  = (isset($_POST['compSupp']) ? $_POST['compSupp'] : NULL);
$pkgList  = (isset($_POST['compPkg'])  ? $_POST['compPkg'] : NULL);
$measList = (isset($_POST['compMeas']) ? $_POST['compMeas'] : NULL);
$vendList = (isset($_POST['compVend']) ? $_POST['compVend'] : NULL);

// Add HTML header content
//
require_once "includes/header.php";
?>
  <h1>Add a Component</h1>
<?php if(isset($_SESSION['errmsg']) && ($_SESSION['errmsg'] != ""))
{
    echo "<p class=\"error\">{$_SESSION['errmsg']}</p>";
    $_SESSION['errmsg'] = "";
}
?>

  <form action="addComponent.php" method="post" name="componentForm">
  <p><label for="compName">Name*:</label> <input type="text" name="compName"<?php if($_SESSION['message'] == "error") showExistVal("compName"); ?> ></p>
  <p><label for="compQuant">Quantity*:</label> <input type="text" name="compQuant"<?php if($_SESSION['message'] == "error") showExistVal("compQuant"); ?>></p>
  <p><label for="compCat[]">Category:</label> <select name="compCat[]" multiple="multiple" size="3">
<?php

buildOptList("Category", $db, $catList);

?>
    </select> 
    <p><label for="compPartNo">Part Number:</label> <input type="text" name="compPartno"<?php if($_SESSION['message'] == "error") showExistVal("compPartno"); ?>></p>
    <p><label for="compVal">Value:</label> <input type="text" name="compVal"<?php if($_SESSION['message'] == "error") showExistVal("compVal"); ?>></p>
    <p><label for="compMeas">Measure:</label> <select name="compMeas">
    <option value="">--Select from list--</option>
<?php

buildOptList("Measure", $db, $measList);

?>
    </select> 
    <p><label for="compPins">Pins:</label> <input type="text" name="compPins"<?php if($_SESSION['message'] == "error") showExistVal("compPins"); ?>></p>

    <p><label for="compPkg">Package:</label> <select name="compPkg">
    <option value="">--Select from list--</option>
<?php

buildOptList("Package", $db, $pkgList);

?>
    </select> 
    <p><label for="compVend">Vendor:</label> <select name="compVend">
    <option value="">--Select from list--</option>
<?php

buildOptList("Vendor", $db, $vendList);

?>
    </select> 
    <p><label for="compSupp[]">Suppliers:</label> <select name="compSupp[]" multiple="multiple" size="3">
<?php

buildOptList("Supplier", $db, $supList);

?>
    </select> 
    <p><input type="submit" value="Add Component"</p>
  </form>

<?php 
// Add HTML footer content
//
require_once "includes/footer.php";
