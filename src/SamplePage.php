<?php include "../inc/dbinfo.inc"; ?>

<html>
<body>
<h1>Sample page</h1>
<?php

/* Connect to PostgreSQL and select the database. */
$constring = "host=" . DB_SERVER . " dbname=" . DB_DATABASE . " user=" . DB_USERNAME . " password=" . DB_PASSWORD ;
$connection = pg_connect($constring);

if (!$connection){
 echo "Failed to connect to PostgreSQL";
 exit;
}

/* Ensure that the EMPLOYEESNEW table exists. */
VerifyEmployeesTable($connection, DB_DATABASE);

/* If input fields are populated, add a row to the EMPLOYEESNEW table. */
$employee_name = htmlentities($_POST['NAME']);
$employee_address = htmlentities($_POST['ADDRESS']);
$employee_age = htmlentities($_POST['AGE']); // new field
$employee_employed = isset($_POST['EMPLOYED']) ? 'true' : 'false'; // new field

if (strlen($employee_name) || strlen($employee_address) || strlen($employee_age)) {
    AddEmployee($connection, $employee_name, $employee_address, $employee_age, $employee_employed);
}

?>

<!-- Input form -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <table border="0">
    <tr>
      <td>NAME</td>
      <td>ADDRESS</td>
      <td>AGE</td> <!-- new field -->
      <td>EMPLOYED</td> <!-- new field -->
    </tr>
    <tr>
      <td>
    <input type="text" name="NAME" maxlength="45" size="30" />
      </td>
      <td>
    <input type="text" name="ADDRESS" maxlength="90" size="60" />
      </td>
      <td>
    <input type="text" name="AGE" maxlength="3" size="5" /> <!-- new field -->
      </td>
      <td>
    <input type="checkbox" name="EMPLOYED" value="true" /> <!-- new field -->
      </td>
      <td>
    <input type="submit" value="Add Data" />
      </td>
    </tr>
  </table>
</form>
<!-- Display table data. -->
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td>ID</td>
    <td>NAME</td>
    <td>ADDRESS</td>
    <td>AGE</td> <!-- new field -->
    <td>EMPLOYED</td> <!-- new field -->
  </tr>

<?php

$result = pg_query($connection, "SELECT * FROM EMPLOYEESNEW");

while($query_data = pg_fetch_row($result)) {
  echo "<tr>";
  echo "<td>",$query_data[0], "</td>",
       "<td>",$query_data[1], "</td>",
       "<td>",$query_data[2], "</td>",
       "<td>", $query_data[3], "</td>", // new field
       "<td>", ($query_data[4] === 't') ? 'Yes' : 'No', "</td>"; // new field
  echo "</tr>";
}
?>
</table>

<!-- Clean up. -->
<?php

  pg_free_result($result);
  pg_close($connection);
?>
</body>
</html>


<?php

/* Add an employee to the table. */
function AddEmployee($connection, $name, $address, $age, $employed) {
   $n = pg_escape_string($name);
   $a = pg_escape_string($address);
   $g = pg_escape_string($age); // new field
   $e = ($employed === 'true') ? 'TRUE' : 'FALSE'; // new field
   $query = "INSERT INTO EMPLOYEESNEW (NAME, ADDRESS, AGE, EMPLOYED) VALUES ('$n', '$a', '$g', $e);";

   if(!pg_query($connection, $query)) echo("<p>Error adding employee data.</p>"); 
}

/* Check whether the table exists and, if not, create it. */
function VerifyEmployeesTable($connection, $dbName) {
  if(!TableExists("EMPLOYEESNEW", $connection, $dbName))
  {
     $query = "CREATE TABLE EMPLOYEESNEW (
         ID serial PRIMARY KEY,
         NAME VARCHAR(45),
         ADDRESS VARCHAR(90),
         AGE INT,
         EMPLOYED BOOLEAN
       )";

     if(!pg_query($connection, $query)) echo("<p>Error creating table.</p>"); 
  }
}
/* Check for the existence of a table. */
function TableExists($tableName, $connection, $dbName) {
  $t = strtolower(pg_escape_string($tableName)); //table name is case sensitive
  $d = pg_escape_string($dbName); //schema is 'public' instead of 'sample' db name so not using that

  $query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t';";
  $checktable = pg_query($connection, $query);

  if (pg_num_rows($checktable) >0) return true;
  return false;

}
?>                        