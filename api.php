<?php
/**
 * Automatic Api Rest
 *
 * @package  Automatic Api Rest
 * @author   Alejandro Esquiva Rodríguez [@alex_esquiva] <alejandro@geekytheory.com>
 * @license  Apache License, Version 2.0
 * @link     https://github.com/GeekyTheory/Automatic-API-REST
 */
require_once 'inc/functions.php';

$blacklist = new BlackList();
$objectTools = new Tools();
/**
 * URL FORMAT
 * http://<DOMAIN>/api/<METHOD>/<table>/<column1>-<column2>-<columnN>/?=<OPTIONAL PARAMETERS>
 *
 * URL EXAMPLE
 * http://localhost/AutomaticApiRest/api/get/country/Continent-Capital/?f=table&o=Name&s=asc&l=0,100
 */
//http://<DOMAIN>/api/<table>/<column1>-<column2>-<columnN>/?=<OPTIONAL PARAMETERS>

// QUERY Variables
$method = "";
$table = "";
$columns="";
$order="";
$sort="";
$limit="";
$where="";
$format="";
$option="";

$path = "";
$params = array();
$parts = explode('/', $_SERVER['REQUEST_URI']);

//put option
$idcol="";
$idval="";
$_PUT=array();


//Get method, table and columns
for ($i = 0; $i < count($parts); $i++) {   
	
	if ($parts[$i]=="api"){

        //convert string $method to lowercase
        $method = strtolower ($parts[$i+1]);
        $table = $parts[$i+2];

        if(!empty($parts[$i+3])) $columns = str_replace("-",",",$parts[$i+3]);

        for($j=0;$j<$i;$j++){
           $path .= $parts[$j]."/";
        }
        break;
    }
	
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Others parameter for get call
    if(isset($_GET["o"])) $order = $_GET["o"];
    if(isset($_GET["s"])) $sort = $_GET["s"];
    if(isset($_GET["l"])) $limit = $_GET["l"];
    if(isset($_GET["w"])) $where = $_GET["w"];
    if(isset($_GET["f"])) $format = $_GET["f"];
    if(isset($_GET["opt"])) $option = $_GET["opt"];    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Others parameter for get call
        if(isset($_POST["o"])) $order = $_POST["o"];
        if(isset($_POST["s"])) $sort = $_POST["s"];
        if(isset($_POST["l"])) $limit = $_POST["l"];
        if(isset($_POST["w"])) $where = $_POST["w"];
        if(isset($_POST["f"])) $format = $_POST["f"];
        if(isset($_POST["opt"])) $option = $_POST["opt"];
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $ppurl=explode('/', $_SERVER['REQUEST_URI']);
    if($method == "put"){
        for ($i = 0; $i < count($ppurl); $i++) { 
            if ($ppurl[$i]=="api"){                
                $idcol=$ppurl[$i+3];
                $idval=$ppurl[$i+4];                
                break;
            }
        }
        parse_str(file_get_contents('php://input'), $_PUT);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    //print_r($_REQUEST);die;
    $ppurl=explode('/', $_SERVER['REQUEST_URI']);
    if($method == "delete"){
        for ($i = 0; $i < count($ppurl); $i++) { 
            if ($ppurl[$i]=="api"){                
                $idcol=$ppurl[$i+3];
                $idval=$ppurl[$i+4];                
                break;
            }
        }
        parse_str(file_get_contents('php://input'), $_PUT);
    }
}



// Delimit formats
if($format == "tree" || $format == "table") $format = "";


// Action API
if($method=="get"){
    $objectTools->getData($table,$columns,$order,$sort,$limit,$where,$format,$option);
}elseif($method=="post"){    
    $objectTools->postData($table,$_POST);
}elseif($method=="put"){
    $objectTools->putData($table,$idcol,$idval,$_PUT);
}elseif($method=="delete"){
    $objectTools->deleteData($table,$idcol,$idval);
}else{
    die($objectTools->JSONError(301));
}

?>