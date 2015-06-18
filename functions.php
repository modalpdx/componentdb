<?php

// Turn on all error reporting.
//
error_reporting( E_ALL );
ini_set('display_errors', 'On');


// Connect to the database.
//
function connectDB()
{
    // Change the next line to include your DB host, DB user, DB user
    // password, and DB name, each in double quotes. This won't work
    // without the edits!
    //
    $db = new mysqli("dbhost", "dbuser", "dbpassword", "dbname");

    if ($db->connect_errno) {
      echo "ERROR: Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
      $_SESSION['message'] = "error";
    }
    return $db;
}


// Grab all ID and name entries for a single table
//
function allIdName($dbTable, $db) {
  if (! ($idNameList = $db->query( "SELECT ID, name FROM {$dbTable}" ))) {
    echo "Name query failed: (" . $db->errno . ") " . $db->error;
    $_SESSION['message'] = "error";
  }
  return $idNameList;
}


// Grab all Vendor or Supplier entries (same table structure for both).
// This WILL NOT WORK with any tables other than Vendor or Supplier!
//
function allVendSupp($dbTable, $db) {
    $queryStr = "SELECT A.ID, A.name, A.address1, A.address2, A.address3, 
        A.locality, A.region, A.postalcode, A.web, A.phone, C.name AS countryname
        FROM {$dbTable} A
        INNER JOIN Country C ON C.ID = A.CountryID";
  if (! ($vendSuppList = $db->query( $queryStr ))) {
    echo "Name query failed: (" . $db->errno . ") " . $db->error;
    $_SESSION['message'] = "error";
  }
  return $vendSuppList;
}



// Check for duplicate name
//
function isUniqueName($newName, $dbTable, $db) {
  if (! ($nameList = $db->query( "SELECT name FROM {$dbTable} WHERE name=\"{$newName}\"" ))) {
    echo "Name query failed: (" . $db->errno . ") " . $db->error;
    $_SESSION['message'] = "error";
  }
  return mysqli_num_rows($nameList);
}


// Delete an item by ID
//
function deleteById($tblName, $itemId, $db) {
  if (! ($db->query ( "DELETE FROM {$tblName} WHERE id={$itemId}" ))) {
    echo "Delete failed: (" . $db->errno . ") " . $db->error;
    $_SESSION['message'] = "error";
  }
}


// Build the HTML option tags for a select list.
// 
// An optional array of match-able IDs can be passed as a
// third parameter.
//
function buildOptList($listCol, $db, $listColID = NULL)
{
  $inId = NULL;  // Holds the ID attribute of a record
  $inCol = NULL; // Holds the name attribute of a record

  // Prepare a statement to select rows in a user-specified table
  //
  if (! ($collector = $db->prepare ("SELECT DISTINCT id, name FROM {$listCol} ORDER BY name" ))) {
    echo "Prepare failed:(" . $db->errno . ") " . $db->error;
    $_SESSION['message'] = "error";
  }
  
  // Bind parameters to be used in the prepared statement
  //
  if (! $collector->bind_result( $inId, $inCol )) {
    echo "Binding output parameters failed: (" . $collector->errno . ") " . $collector->error;
    $_SESSION['message'] = "error";
  }
  
  // Execute the prepared statement with the bound parameters
  //
  if (! $collector->execute ()) {
    echo "Execute failed: (" . $collector->errno . ") " . $collector->error;
    $_SESSION['message'] = "error";
  }
  
  // Required for num_rows() to work
  //
  $collector->store_result();
  
  // If we got more than 0 results, populate the category menu with categories.
  //
  if ($collector->num_rows() > 0) {
    while ( $collector->fetch() ) {
        echo "\t\t\t<option value=\"{$inId}\"";
        if($listColID && in_array($inId, $listColID))
        {
            echo " selected";
        }
        echo ">{$inCol}</option>\n";
    }
  }
  
  $collector->close ();
}


// Get the value of a form element from POST
//
function grabPostAttr($postAttr) {
  $inPostAttr = NULL;
  if ((isset($_POST[$postAttr] )) && (!empty($_POST[$postAttr]))) {
      $inPostAttr = htmlspecialchars($_POST[$postAttr]);
  } 
  return $inPostAttr;
}


// Get the value of a form ARRAY element from POST
//
function grabPostArrAttr($postAttr, $arrIdx) {
  $inPostAttr = NULL;
  if ((isset($_POST[$postAttr][$arrIdx])) && (!empty($_POST[$postAttr][$arrIdx]))) {
      $inPostAttr = $_POST[$postAttr][$arrIdx];
  } 
  return $inPostAttr;
}


// Display existing form values if they were passed to the page
// via $_POST[] (ex: when a form reloads b/c of an error)
//
function showExistVal($formEl, $fallBack = NULL)
{
  $inPostAttr = grabPostAttr($formEl);
  if($inPostAttr != NULL)
  { 
    echo " value=\"{$inPostAttr}\"";
  }
  else
  {
    echo " value=\"{$fallBack}\"";
  }
}

