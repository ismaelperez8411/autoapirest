<?php
include_once 'functions.php';

class Tools
{
    private $db;       //The db handle
    public  $num_rows; //Number of rows
    public  $last_id;  //Last insert id
    public  $aff_rows; //Affected rows

    public function __construct()
    {
        //require 'config.php';
        $this->db = pg_connect("host=".SERVER." port=".PORT." dbname=".DB." user=".USER." password=".PASS."");
        if (!$this->db) exit();
    }
    
    public function connectDB(){        
        return $this->db = pg_connect("host=".SERVER." port=".PORT." dbname=".DB." user=".USER." password=".PASS."");
    }

    public function disconnectDB()
    {
        pg_close($this->db);
    }

    // For SELECT
    // Returns one row as object
    public function getRow($sql)
    {
        $result = pg_query($this->db, $sql);
        $row = pg_fetch_object($result);
        if (pg_last_error()) exit(pg_last_error());
        return $row;
    }

    // For SELECT
    // Returns an array of row objects
    // Gets number of rows
    public function getArraySQL($sql)
    {
        $result = pg_query($this->connectDB(), $sql);
        if (pg_last_error()) die($this->JSONError(301,pg_last_error()));
        $this->num_rows = pg_num_rows($result);
        $rows = array();
        while ($item = pg_fetch_array($result, NULL ,PGSQL_ASSOC)) {
            $rows[] = $item;
        }
        return $rows;
    }

    // For SELECT
    // Returns one single column value as a string
    public function getCol($sql)
    {
        $result = pg_query($this->db, $sql);
        $col = pg_fetch_result($result, 0);
        if (pg_last_error()) exit(pg_last_error());
        return $col;
    }

    // For SELECT
    // Returns array of all values in one column
    public function getColValues($sql)
    {
        $result = pg_query($this->db, $sql);
        $arr = pg_fetch_all_columns($result);
        if (pg_last_error()) exit(pg_last_error());
        return $arr;
    }

    // For INSERT
    // Returns last insert $id
    public function insert($sql, $id='id')
    {
        $sql = rtrim($sql, ';');
        $sql .= ' RETURNING '.$id;
        $result = pg_query($this->db, $sql);
        if (pg_last_error()) exit(pg_last_error());
        $this->last_id = pg_fetch_result($result, 0);
        return $this->last_id;
    }

    // For UPDATE, DELETE and CREATE TABLE
    // Returns number of affected rows
    public function setDataBySQL($sql)
    {
        $result = pg_query($this->db, $sql." RETURNING *");
        //print_r($result);die;
        if (pg_last_error())  die($this->JSONError(303,pg_last_error()));
        $this->aff_rows = pg_affected_rows($result);
        $rows = array();
        while ($item = pg_fetch_array($result, NULL ,PGSQL_ASSOC)) {
            $rows[] = $item;
        }
        return $rows;
        //return $this->aff_rows;
    }

    public function displayError($title,$message){
        ?>
        <div class="row">
            <div class="col-sm-4">

            </div>
            <div class="col-sm-4">
                <div class="page-header">
                    <h1><?php echo $title; ?></h1>
                </div>
                <div class="alert alert-info">
                    <?php echo $message; ?>
                </div>
            </div>
            <div class="col-sm-4">

            </div>
        </div>
        <?php
    }

    public function JSONError($code,$details = ""){
        $json = "";
        
        if($code == 401){
            $error[0] = array("code" => $code,"message" => "Unauthorized");
            $json = array ("errors" => $error);
        }
        if($code == 301){
            $error[0] = array("code" => $code,"message" => "Invalid Parameters: ".$details);
            $json = array ("errors" => $error);
        }
        if($code == 302){
            $error[0]= array("code" => $code,"message" => "Empty Data");
            $json = array ("errors" => $error);
        }
        if($code == 303){
            $error[0]= array("code" => $code,"message" => "Insert data error: ".$details);
            $json = array ("errors" => $error);
        }
        echo json_encode($json);        
    }

    public function getCurlJson($url,$variablesJson=''){
        $ch = curl_init();                    // Initiate cURL
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $variablesJson);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          //  'Content-Type: application/json',
            //'Content-Length: ' . strlen($variablesJson))
        //);

        $result = curl_exec($ch);
	    return $result;
    }

    /**
     * Display a table from SQL sentence
     * @param type $sql
     */
    public function displayTable($sql){
        //Creamos la conexión
        //die($sql);
        $conexion = $this->connectDB();
        //generamos la consulta
        if(!$result = pg_query($conexion, $sql)) die();
        $rawdata = array();
        //guardamos en un array multidimensional todos los datos de la consulta
        $i=0;
        while($row = pg_fetch_array($result))
        {
            $rawdata[$i] = $row;
            $i++;
        }
        $this->disconnectDB();

        
        //DIBUJAMOS LA TABLA
        echo '<table class="table table-striped table-bordered table-condensed">';

        if(count($rawdata)==0)
            echo '<tr><td>La tabla está vacia..</td></tr>';
        else
        {
            $columnas = count($rawdata[0])/2;
            //echo $columnas;
            $filas = count($rawdata);
            //echo "<br>".$filas."<br>";
            //Añadimos los titulos
                
            for($i=1;$i<count($rawdata[0]);$i=$i+2){
                next($rawdata[0]);
                echo "<th><b>".key($rawdata[0])."</b></th>";
                next($rawdata[0]);
            }        
            for($i=0;$i<$filas;$i++){
                echo "<tr>";
                for($j=0;$j<$columnas;$j++){
                    echo "<td>".$rawdata[$i][$j]."</td>";
                    
                }
                echo "</tr>";
            }	
        }	
        echo '</table>';
    }
    /**
     * Return all the columns from a table
     * @param type $table
     * @return type
     */
    public function getFieldsByTable($table){
        
        $conexion = $this->connectDB();
    	
        # Consulta SQL que devuelve los campos de cada tabla
        $campos = pg_query($this->db,"select column_name from information_schema.columns where table_schema||'.'||table_name = '".$table."'") or die($this->JSONError(301));

        $this->disconnectDB();
        
        $count = 0;            
        # Muestra como tabla HTML los detalles de los campos de la tabla correspondiente
        if(pg_num_rows($campos)) {
            while($detalles = pg_fetch_row($campos)) {
                $myArray[$count] = $detalles[0];   
                $count++;
            }
        }
        
        return $myArray;

    }
    
    public function getData($table,$columns="",$order="",$sort="",$limit="",$where="",$format="",$option=""){
		
        $blacklist = new BlackList();

        /**
         * check the blacklist
         */
        if($columns!=""){
            $exist = $blacklist->existItem("G",$table,"*");
            if(!$exist){
                $exist = $blacklist->existItem("G",$table,$columns);
            }
        }else{
            $exist = $blacklist->existItem("G",$table,"*");
        }

        /**
         * If the query is not allowed -> die
         */
        if($exist){
            die($this->JSONError(401));
        }

        /**
         * Create the sql sentence with the input parameters
         */
		
        if (strpos($columns,'?') !== false) {
            $columns = "";
        }

        if($columns!=""){

            //get the fields which are not in the black list
            $fields = explode(",", $columns);
            $fields_allowed = "";
            for($i=0;$i<count($fields);$i++){
                if(!$blacklist->existItem("G", $table, $fields[$i])){
                    if($fields_allowed == ""){
                        $fields_allowed = $fields[$i];
                    }else{
                        $fields_allowed = $fields_allowed.",".$fields[$i];
                    }
                }
            }


            if($where!=""){
                $where = str_replace(":","=",$where);
            }

            if($order!=""){
                if($limit!=""){
                    if($where!=""){
                        $sql = "SELECT ".$fields_allowed." FROM ".$table." WHERE $where ORDER BY ".$order." ".$sort." LIMIT ".$limit.";";
                    }else{
                        $sql = "SELECT ".$fields_allowed." FROM ".$table." ORDER BY ".$order." ".$sort." LIMIT ".$limit.";";
                    }
                }else{
                    if($where!=""){
                        $sql = "SELECT ".$fields_allowed." FROM ".$table." WHERE $where ORDER BY ".$order." ".$sort.";";
                    }else{
                        $sql = "SELECT ".$fields_allowed." FROM ".$table." ORDER BY ".$order." ".$sort.";";
                    }
                }
            }else{
                if($limit!=""){
                    if($where!=""){
                        $sql = "SELECT ".$fields_allowed." FROM ".$table." WHERE $where LIMIT ".$limit.";";
                    }else{
                        $sql = "SELECT ".$fields_allowed." FROM ".$table." LIMIT ".$limit.";";
                    }
                }else{
                    if($where!=""){
                        $sql = "SELECT ".$fields_allowed." FROM ".$table." WHERE $where;";
                    }else{
                        $sql = "SELECT ".$fields_allowed." FROM ".$table.";";
                    }
                }
            }

        }else{
            //get the fields which are not in the black list
            $fields = $this->getFieldsByTable($table);
            $fields_allowed = "";
            for($i=0;$i<count($fields);$i++){
                if(!$blacklist->existItem("G", $table, $fields[$i])){
                    if($fields_allowed == ""){
                        $fields_allowed = $fields[$i];
                    }else{
                        $fields_allowed = $fields_allowed.",".$fields[$i];
                    }
                }
            }

            if($order!=""){
                if($limit!=""){
                    $sql = "SELECT $fields_allowed FROM ".$table." ORDER BY ".$order." ".$sort." LIMIT ".$limit.";";
                }else{
                    $sql = "SELECT $fields_allowed FROM ".$table." ORDER BY ".$order." ".$sort.";";
                }
            }else{
                if($limit!=""){
                    $sql = "SELECT $fields_allowed FROM ".$table." LIMIT ".$limit.";";
                }else{
                    $sql = "SELECT $fields_allowed FROM ".$table.";";
                }
            }
        }

        $function = "";

        if($format!=""){
            $function = $format;
        }else{
            $function = "json";
        }

        if($option!=""){
            $opt = $option;
        }else{
            $opt = "";
        }

        if($function=="json"){
			header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');

            if($opt == "numItem"){
                $conexion = $this->connectDB();
                $result = pg_query($conexion,$sql);
                $rawdata = pg_num_rows($result);
                $this->disconnectDB();
            }else{
                $rawdata = $this->getArraySQL($sql);                
            }

            if(empty($rawdata)) die ($this->JSONError (302));

            $indices = array();
            $count = 0;
            
            /*for($i=1;$i<count($rawdata[0]);$i=$i+2){
                next($rawdata[0]);
                $indices[$count] = key($rawdata[0]);
                $count++;
                next($rawdata[0]);
            }*/
            foreach($rawdata[0] as $k=>$v){
                $indices[$count] = $k;
                $count++;
                //next($rawdata[0]);
            }
            $json["data"] = $rawdata;
            $json["dbInfo"] = $indices;

            //Clean the page
            ob_end_clean();
            //Output
	    echo json_encode($json);

        }else if($function=="xml"){

        }else if($function=="table"){
            require_once 'mod/header.php';
            $this->displayTable($sql);
            require_once 'mod/footer.php';
        }else if($function=="tree"){
            require_once 'mod/header.php';
            $rawdata = $this->getArraySQL($sql);

            $keyarray = "";
            $valuearray = "";
            for($i=0;$i<count($rawdata[0]);$i++){
                $keyarray[$i] = key($rawdata[0]);
                next($rawdata[0]);
            }
            for($i=0;$i<count($rawdata);$i++){
                for($j=0;$j<count($rawdata[$i])/2;$j++){
                    $valuearray[$i][$j] = $rawdata[$i][$j];
                }
            }
            $data = "";

            echo "<ol>";
            for($i=0;$i<count($valuearray);$i++){
                echo "<li>";
                echo "<br>";
                echo "<ul>";
                $count = 0;
                for($j=0;$j<count($valuearray[$i]);$j++){
                    echo "<li><b>".$keyarray[$count]."</b>: ".$valuearray[$i][$j]."</li>";
                    $count++;
                    echo "<li><b>".$keyarray[$count]."</b>: ".$valuearray[$i][$j]."</li>";
                    $count++;
                }
                echo "</ul>";
                echo "</li>";
            }
            echo "</ol>";
            require_once 'mod/footer.php';
        }else{
            die($this->JSONError(301));
        }

    }

    public function postData($table,$post_parameters){
        //print_r($post_parameters);die;
        $blacklist = new BlackList();
        /**
         * check the blacklist
         */
        $values = "";
        $columns = "";
        $values_array = array();
        $columns_array = array();
        $first_iteration = true;
        $counter = 0;

        foreach($post_parameters as $field => $value) {
            // Detect if it is a text or number
            $value = (is_numeric($value) ? $value : "'".$value."'");
            // join the string with (,) ie: value1,value2,value3
            $values .= ($first_iteration ? $value : ",".$value);
            $columns .= ($first_iteration  ? $field : ",".$field);
            $values_array[$counter] = $value;
            $columns_array[$counter] = $field;
            $first_iteration = false;
            $counter++;
        }

        if($blacklist->existItem("G",$table,"*")) die($this->JSONError(401));
        if(!empty($post_parameters)){
            for($i=0;$i<count($columns_array);$i++){
                if ($blacklist->existItem("G", $table, $columns_array[$i])) die($this->JSONError(401));
            }
        }

        /**
         * Create the sql sentence with the post parameters
         */

        if($values != ""){
            $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        }
        //die($sql);
        $function = "json";
        if($function=="json"){
			header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');

            $result = $this->setDataBySQL($sql);
            //print_r($result);die;
            if(!$result) die ($this->JSONError (303));
            $indices = array();
            $rawdata = array();
            //var_dump($columns_array);die;
            $i=0;
            foreach($result[0] as $k=>$v){
                $indices[] = $k;
                $i++;
            }
            
            $json["data"] = $result;
            $json["dbInfo"] = $indices;
            $json["dbMsg"] = "El registro ha sido Adicionado de forma exitosa.";

            echo json_encode($json);
        }else if($function=="xml"){

        }else{
            die($this->JSONError(301));
        }
    }

    // public function putData($table,$idcol,$idval,$post_parameters){
    
    //     $blacklist = new BlackList();
    //     /**
    //      * check the blacklist
    //      */
    //     $values = "";
    //     $columns = "";
    //     $values_array = array();
    //     $columns_array = array();
    //     $first_iteration = true;
    //     $counter = 0;

    //     $setValues="";

    //     foreach($post_parameters as $field => $value) {
    //         // Detect if it is a text or number
    //         $value = (is_numeric($value) ? $value : "'".$value."'");
    //         // join the string with (,) ie: value1,value2,value3
    //         $values .= ($first_iteration ? $value : ",".$value);
    //         $setValues .= ($first_iteration ? $field." = ".$value : ",$field = ".$value);
    //         $columns .= ($first_iteration  ? $field : ",".$field);
    //         $values_array[$counter] = $value;
    //         $columns_array[$counter] = $field;
    //         $first_iteration = false;
    //         $counter++;
    //     }

    //     if($blacklist->existItem("G",$table,"*")) die($this->JSONError(401));
    //     if(!empty($post_parameters)){
    //         for($i=0;$i<count($columns_array);$i++){
    //             if ($blacklist->existItem("G", $table, $columns_array[$i])) die($this->JSONError(401));
    //         }
    //     }

    //     /**
    //      * Create the sql sentence with the post parameters
    //      */
    //     $sql="";
    //     if($values != ""){
    //         $sql = "UPDATE $table SET $setValues WHERE $idcol = $idval ";
    //     }
       
    //     //echo json_encode($sql);die;
    //     $function = "json";
    //     if($function=="json"){
	// 		header("Access-Control-Allow-Origin: *");
    //         header('Content-Type: application/json');

    //         $result = $this->setDataBySQL($sql);

    //         if(!$result) die ($this->JSONError (303));
    //         $indices = array();
    //         $rawdata = array();
    //         //var_dump($columns_array);
    //         for($i=0;$i<count($columns_array);$i++){
    //             $rawdata[0][$i] = $values_array[$i];
    //             $rawdata[0][$columns_array[$i]] = $values_array[$i];
    //             $indices[$i] = $columns_array[$i];
    //             $i++;
    //         }

    //         $json["data"] = $rawdata;
    //         $json["dbInfo"] = $indices;
    //         $json["dbMsg"] = "El registro ha sido Actualizado satisfactoriamente.";

    //         echo json_encode($json);
    //     }else if($function=="xml"){

    //     }else{
    //         die($this->JSONError(301));
    //     }
    // }

    public function deleteData($table,$idcol,$idval){
        
        $sql = "DELETE FROM $table WHERE $idcol = $idval ";
        
        $function = "json";
        if($function=="json"){
			header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');

            $result = $this->setDataBySQL($sql);

            if(!$result) die ($this->JSONError (303));
            
            $json=array();
            $json["data"] = $result;
            $json["dbMsg"] = "El registro ha sido Eliminado satisfactoriamente.";

            echo json_encode($json);
        }else if($function=="xml"){

        }else{
            die($this->JSONError(301));
        }
    }
    

}

?>
