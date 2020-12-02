<?php

while($tabla = pg_fetch_row($tablas)) {
        $nombreTabla = $tabla[0];
        if($nombreTabla==$_GET["t"]){    	
            # Consulta SQL que devuelve los campos de cada tabla
            $campos = pg_query($conexion,"select column_name from information_schema.columns where table_schema||'.'||table_name = '".$nombreTabla."'") or die(require_once 'mod/footer.php');
            //Número de campos
            $num_campos = pg_num_rows($campos);// -> num_rows;
            $count = 0;            
            # Muestra como tabla HTML los detalles de los campos de la tabla correspondiente
            if(pg_num_rows($campos)) {
                    echo '<form>';
                    echo '<table class="table table-striped table-bordered table-condensed" cellpadding="0" cellspacing="0" style="margin-top:6%;">
                            <thead>
                                <th>Column</th>
                                <th>API Link</th>
                                <th>Show Table</th>
                                <th>Select</th>
                                <th>Privacity</th>
                            </thead><tbody>';
                    while($detalles = pg_fetch_row($campos)) {
                            echo '<tr>';
                                echo '<td style="text-align:left;">';
                                    echo $detalles[0];
                                echo '</td>';
                            
                                echo '<td style="text-align:left;">';
                                    $urlJsonAPI = $pathFolderAPI."/api/get/".$nombreTabla."/$detalles[0]/";
                                    echo '<a target="_blank" href="'.$urlJsonAPI.'">';
                                    echo $urlJsonAPI;
                                    echo '</a>';
                                echo '</td>';
                            
                                echo '<td>';
                                    $urlJson = $pathFolder."/getData.php?f=table&t=".$nombreTabla."&c=".$detalles[0];
                                    echo '<a href="'.$urlJson.'" style="color:#fff;" target= "_blank">';       
                                    echo '<img width="30px" src="assets/img/tabla.jpg">';       
                                    echo '</img>';
                                    echo '</a>';
                                echo '</td>';
                            
                                echo '<td>';
                                    echo '<input id="cbc'.$count.'" type="checkbox" value='.$detalles[0].' onchange="customSelect('.$num_campos.')">';
                                echo '</td>';
                                echo '<td>';
                                    if($blacklist->existItem("G", $nombreTabla, $detalles[0]) || $blacklist->existItem("G", $nombreTabla, "*") ){
                                        echo "<input id='private$count' type='checkbox' checked=true onchange='updatePrivacity(\"$count\",\"G\",\"$nombreTabla\",\"$detalles[0]\")'>";
                                    }else{
                                        echo "<input id='private$count' type='checkbox' onchange='updatePrivacity(\"$count\",\"G\",\"$nombreTabla\",\"$detalles[0]\")'>";
                                    }
                                echo '</td>';
                            echo '</tr>';
                            $count++;
                    }
                    echo '</tbody></table><br />';       
                    echo '</form>';
                    
            }
        }

    }