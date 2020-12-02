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
require_once("inc/autentification.php");
require_once 'mod/header.php';

//VARIABLES DE PATH 
if($_SERVER['SERVER_PORT'] == 80){
    $urlNow = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    $pathFolderAPI = "http://".$_SERVER['HTTP_HOST']."/autoapirest";
}else{
    $urlNow = "http://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF'];
    $pathFolderAPI = "http://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT']."/autoapirest";
}
$pathFolder = dirname($urlNow);

# Establecer la conexión a la Base de Datos
$tool = new Tools();
$conexion = $tool->connectDB();

# Consulta SQL que devuelve los nombres de las tablas de la Base de Datos

$tablas = pg_query($conexion," SELECT foo.schemaname||'.'||foo.tablename FROM 
                                (
                                    (SELECT schemaname,tablename::text as tablename FROM pg_catalog.pg_tables 
                                        WHERE schemaname<>'pg_catalog' AND schemaname<>'topology' AND schemaname<>'cadata' AND schemaname<>'information_schema' 
                                        ORDER BY schemaname ASC)
                                    UNION
                                    (select schemaname,viewname::text as tablename from pg_catalog.pg_views 
                                    WHERE schemaname<>'pg_catalog' AND schemaname<>'topology' AND schemaname<>'cadata' AND schemaname<>'information_schema' 
                                    ORDER BY schemaname ASC)
                                ) foo ORDER BY foo.schemaname") or die(require_once 'mod/footer.php');

//Objecto BlackList
$blacklist = new BlackList();

if(!isset($_GET["t"])){
    require_once 'mod/modTable.php';
}else{
    require_once 'mod/modFields.php';
}
#Cerrar la conexión a la Base de Datos

pg_close($conexion);

if(isset($_GET["t"])){
    $urlJsonAPI = $pathFolderAPI."/api/get/".$_GET["t"]."/";
    $viewTable = $pathFolder."/getData.php?f=table&t=".$_GET["t"];
    $viewTree = $pathFolder."/getData.php?f=tree&t=".$_GET["t"];
    $advance = $pathFolder."/advance.php?t=".$_GET["t"];
    
    $objectTools = new Tools();
    $fields = $objectTools->getFieldsByTable($_GET["t"]);

    require_once 'mod/modCustomSelect.php';
 //cerrar isset
 }

 require_once 'mod/footer.php';
?>
