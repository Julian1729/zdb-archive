 <?php


/**
*
* Connect to Database
*
*/

$servername = "dlzandersnet.ipagemysql.com";
$username = "dlzanders01";
$password = "j1102hJBJ";
$dbname = "darrenzanders";



// $servername = "localhost";
// $username = "root";
// $password = "root";
// $dbname = "zanders_test";

$conn = new \mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
*
* Logic
*
*/

// names of the needed columns from each visit
$needed_columns = array(
    "wed_morn_shep",
    "wed_morn_shep_elder",
    "wed_morn_shep_publisher",
    "wed_morn_shep_situation",
    "wed_aft_bro_activities",
    "wed_aft_sis_activities",
    "thurs_morn_shep",
    "thurs_morn_shep_elder",
    "thurs_morn_shep_publisher",
    "thurs_morn_shep_situation",
    "thurs_aft_bro_activities",
    "thurs_aft_sis_activities",
    "fri_aft_bro_activities",
    "fri_aft_sis_activities",
    "visitId"
);

// add "activities" column
try{
  $add_column_statement = "ALTER TABLE `visits` ADD `activities_json` text AFTER `publishernoprayer`";
  $conn->query($add_column_statement);
}catch(\Exception $e){}

// get all visit rows
$select_visit_query = "SELECT * FROM `visits`";
$visits = $conn->query($select_visit_query);

$visitArray = array();

if($visits->num_rows > 0){
    while($row = $visits->fetch_assoc()):
      $info = array();
      foreach ($needed_columns as $column) {
        try {
          $info[$column] = $row[$column];
        } catch (\Exception $e) {
          continue;
        }
      }
      $visitArray[] = $info;
    endwhile;
}


$enter_db = array();

// loop through $visitArray
foreach ($visitArray as $row) {
  // create empty assoc array to be popluted
  $db_activities = array(
    "wed_morn_bro" => array(),
    "wed_morn_sis"=> array(),
    "wed_aft_bro"=> array(),
    "wed_aft_sis"=> array(),
    "thurs_morn_bro"=> array(),
    "thurs_morn_sis"=> array(),
    "thurs_aft_bro"=> array(),
    "thurs_aft_sis"=> array(),
    "fri_morn_bro"=> array(),
    "fri_morn_sis"=> array(),
    "fri_aft_bro"=> array(),
    "fri_aft_sis"=> array()
  );

  // HANDLE SHEPERDING MORNING SHEPERDING CALLS
    // Wednesday
    if($row['wed_morn_shep']){
      // there is a sheperding call
      $db_activities['wed_morn_bro'][] = array(
        'type' => 'sheperding_call',
        'elder' => $row['wed_morn_shep_elder'],
        'publisher' => $row['wed_morn_shep_publisher'],
        'situation' => $row['wed_morn_shep_situation']
      );
    }
    // Thursday
    if($row['thurs_morn_shep']){
      // there is a sheperding call
      $db_activities['thurs_morn_bro'][] = array(
        'type' => 'sheperding_call',
        'elder' => $row['thurs_morn_shep_elder'],
        'publisher' => $row['thurs_morn_shep_publisher'],
        'situation' => $row['thurs_morn_shep_situation']
      );
    }

  // HANDLE ACTIVITIES
    // Wednesday
    $db_activities['wed_aft_bro'] = convertJSON($row['wed_aft_bro_activities']);
    $dv_activities['wed_aft_sis'] = convertJSON($row['wed_aft_sis_activities']);

    // convert to json
    $jsonActivities = json_encode($db_activities);
    // enter into array to be entered into db
    $enter_db[$row['visitId']] = $jsonActivities;

}

// PUSH DATA INTO DB
$query = "UPDATE `visits` SET `activities_json` = ? WHERE `visitId` = ?";
$stmt = $conn->prepare($query);
foreach ($enter_db as $id => $data) {
  $stmt->bind_param("ss", $data, $id);
  if( $stmt->execute() ):
    echo $id . " updated <br />";
  else:
    echo $id . " not updated \"$conn->error\" <br />";
  endif;
}

// DROP UNNECESSARY COLUMNS
$dropColumns = $needed_columns;

unset($dropColumns[array_search('visitId', $dropColumns)]);
foreach ($dropColumns as $c) {
  try{
    $query = "ALTER TABLE `visits` DROP COLUMN `$c`";
    if( $conn->query($query) ):
      echo $c . ' column deleted <br />';
    endif;
  }catch(\Exception $e){
    $c . ' column not deleted<br />';
  }
}

function convertJSON($string){
  if(empty($string)){
    return null;
  }
  $array = json_decode($string, true);
  $new_array = array();
  foreach ($array as $type => $values) {
    $decoded_info = array();
    switch ($type) {
      case 'shep':
        $decoded_info['type'] = 'sheperding_call';
      break;
      case 'bs':
        $decoded_info['type'] = 'bible_study';
      break;
      case 'rv':
        $decoded_info['type'] = 'return_visit';
      break;
      default:
        return null;
      break;
    }
    foreach ($values as $key => $value) {
      switch ($key) {
        case 'pub':
          $decoded_info['publisher'] = $value;
          break;
        case 'sit':
          $decoded_info['situation'] = $value;
        break;
        default:
          $decoded_info[$key] = $value;
          break;
      }
    }
    $new_array[] = $decoded_info;
  }
  // convert back to json
  //$json = json_encode($new_array);
  //return $json;
  return $new_array;
}



?>
