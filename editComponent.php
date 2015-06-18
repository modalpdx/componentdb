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
    $outCMId = $_GET['c'];
}

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
          $_SESSION['errmsg'] = "ERROR: Component quantity must be specified.";
          $validated = FALSE;
      }
  } else {
    $_SESSION['errmsg'] = "ERROR: Component name must be specified.";
    $validated = FALSE;
  }

  // Prepare a statement to update a row in the Component table
  //
  if ($validated === TRUE) {
      if (! ($stmt = $db->prepare ( "UPDATE Component SET
          name = ?,
          pins = ?,
          partno = ?,
          value = ?,
          quantity = ?,
          MeasureID = ?,
          PackageID = ?,
          VendorID = ?
          WHERE ID=?" )
      )) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      // Bind parameters to be used in the prepared statement
      //
      if (! $stmt->bind_param ( 
          "sissiiiii", 
          $inName, 
          $inPins, 
          $inPartno, 
          $inVal, 
          $inQuant, 
          $inMeas, 
          $inPack,
          $inVend,
          $outCMId
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

      //
      // Updating rows in a many-to-many tables seems impossible, or at
      // least so mind-blowingly complex that it is not worth the cycles
      // to accomplish it. We have all the attributes that we need, so
      // just delete all records associated with the component and then
      // add new ones to match the user's input.
      //
    
      // Prepare a statement to delete a row in the Component_Category table
      //
      if (! ($stmt = $db->prepare ( "DELETE FROM Component_Category 
            WHERE compId=?" ))) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      // Bind parameters to be used in the prepared statement
      //
      if (! $stmt->bind_param ( 
          "i", 
          $outCMId
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

      // Prepare a statement to insert a row in the Component_Category table
      //
      if (! ($stmt = $db->prepare ( "INSERT INTO Component_Category (
            compId,
            catId
          ) VALUES (?,?)" )
      )) {
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
                  $outCMId, 
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

      // 
      // As with Component_Category, updating records in the
      // Supplier_Component many-to-many table is too complex to be worth
      // the computing cycles. It makes significantly more sense to delete
      // the rows associated with the current component and simply add new
      // ones that match the user's input.
      //

      // Prepare a statement to delete a row in the Supplier_Component table
      //
      if (! ($stmt = $db->prepare ( "DELETE FROM Supplier_Component
            WHERE compId=?" ))) {
          echo "ERROR: Prepare failed: (" . $db->errno . ") " . $db->error;
          $_SESSION['message'] = "error";
      }

      // Bind parameters to be used in the prepared statement
      //
      if (! $stmt->bind_param ( 
          "i", 
          $outCMId 
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

      // Prepare a statement to insert a row in the Supplier_Component table
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
                  $outCMId, 
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

  // Grab everything we need to grab from $_POST before getting
  // rid of it. We'll need this if the form isn't validated and
  // we want to prefill it up on reload (so the user doesn't have
  // to re-enter all the fields).
  //
  // Note: buildOptList() is written to accept an array of selected
  // values, not a string, so the selected Measure, Package, and Vendor
  // must be in an array. It's easiest to just handle that here.
  //
  $outCMName   = $_POST['compName'];
  $outPins     = $_POST['compPins'];
  $outPartno   = $_POST['compPartno'];
  $outValue    = $_POST['compVal'];
  $outQuant    = $_POST['compQuant'];
  $outMeasID[] = $_POST['compMeas'];
  $outPkgID[]  = $_POST['compPkg'];
  $outVendID[] = $_POST['compVend'];

  // If component categories and/or suppliers were passed to this 
  // script, prepare them for inclusion in the buildOptList() process.
  // Otherwise, make them NULL.
  //
  $catList  = (isset($_POST['compCat'])  ? $_POST['compCat'] : NULL);
  $supList  = (isset($_POST['compSupp']) ? $_POST['compSupp'] : NULL);

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
    $queryStr = "SELECT name, pins, partno, value, quantity, MeasureID, PackageID, VendorID
                 FROM Component
                 WHERE ID=" . $outCMId;

    
    if (! ($result = $db->query($queryStr))) {
        echo "ERROR: Could not retrieve component: (" . $db->errno . ") " . $db->error;
        $_SESSION['message'] = "error";
    }
 
    $compRow = $result->fetch_array(MYSQLI_ASSOC);
    
    // Note: buildOptList() is written to accept an array of selected
    // values, not a string, so the selected Measure, Package, and Vendor
    // must be in an array. It's easiest to just handle that here.
    //
    $outCMName   = $compRow['name'];
    $outPins     = $compRow['pins'];
    $outPartno   = $compRow['partno'];
    $outValue    = $compRow['value'];
    $outQuant    = $compRow['quantity'];
    $outMeasID[] = $compRow['MeasureID'];
    $outPkgID[]  = $compRow['PackageID'];
    $outVendID[] = $compRow['VendorID'];
    
    
    // Grab categories
    //
    $queryStr = "SELECT ID FROM Category C 
        INNER JOIN Component_Category CC ON CC.catID = C.ID
        WHERE CC.compID=" . $outCMId;
    
    if (! ($result = $db->query($queryStr))) {
        echo "ERROR: Could not retrieve categories: (" . $db->errno . ") " . $db->error;
        $_SESSION['message'] = "error";
    }
    
    while($row = $result->fetch_row())
    {
        $catList[] = $row[0];
    }
    
    // Grab suppliers
    //
    $queryStr = "SELECT ID FROM Supplier S 
        INNER JOIN Supplier_Component SC ON SC.supID = S.ID
        WHERE SC.compID=" . $outCMId;

    if (! ($result = $db->query($queryStr))) {
        echo "ERROR: Could not retrieve supplier(s): (" . $db->errno . ") " . $db->error;
        $_SESSION['message'] = "error";
    }
    
    while($row = $result->fetch_row())
    {
        $supList[] = $row[0];
    }
}

// Add HTML header content
//
require_once "includes/header.php";
?>

  <h1>Edit a Component</h1>
<?php if(isset($_SESSION['errmsg']) && ($_SESSION['errmsg'] != ""))
{
    echo "<p class=\"error\">{$_SESSION['errmsg']}</p>";
    $_SESSION['errmsg'] = "";
}
?>

  <form action="editComponent.php?c=<?php echo $outCMId; ?>" method="post" name="componentForm">
  <p><label for="compName">Name*:</label> <input type="text" name="compName" <?php showExistVal("compName", $outCMName); ?>></p>
  <p><label for="compQuant">Quantity*:</label> <input type="text" name="compQuant" value="<?php echo $outQuant; ?>"></p>
  <p><label for="compCat[]">Category:</label> <select name="compCat[]" multiple="multiple" size="3">
<?php

buildOptList("Category", $db, $catList);

?>
    </select> 
    <p><label for="compPartNo">Part Number:</label> <input type="text" name="compPartno" value="<?php echo $outPartno; ?>"></p>
    <p><label for="compVal">Value:</label> <input type="text" name="compVal" value="<?php echo $outValue; ?>"></p>
    <p><label for="compMeas">Measure:</label> <select name="compMeas">
    <option value="">--Select from list--</option>
<?php

buildOptList("Measure", $db, $outMeasID);

?>
    </select> 
    <p><label for="compPins">Pins:</label> <input type="text" name="compPins" value="<?php echo $outPins; ?>"></p>
    <p><label for="compPkg">Package:</label> <select name="compPkg">
    <option value="">--Select from list--</option>
<?php

buildOptList("Package", $db, $outPkgID);

?>
    </select> 
    <p><label for="compVend">Vendor:</label> <select name="compVend">
    <option value="">--Select from list--</option>
<?php

buildOptList("Vendor", $db, $outVendID);

?>
    </select> 
    <p><label for="compSupp[]">Suppliers:</label> <select name="compSupp[]" multiple="multiple" size="3">
<?php

buildOptList("Supplier", $db, $supList);

?>
    </select> 
    <p><input type="submit" value="Edit Component"</p>
  </form>

<?php 
// Add HTML footer content
//
require_once "includes/footer.php";
