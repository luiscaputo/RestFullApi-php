<?php
  require_once '../../vendor/autoload.php';  

  //############### cors do php para api ser acessivel ###############
  header("Access-Control-Allow-Origin:*");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: POST,GET,PUT,DELETE");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
  
  //############### Variavel responsavel por pegar o json(data) ###############
  $dat = json_decode(file_get_contents("php://input"), true);
  
  //############### Upload de arquivos ###############
  if (isset($_FILES["imagem"])) {
      $nomeImage = $_FILES["imagem"];
      $pastDist = "upload";
      move_uploaded_file($nomeImage["tmp_name"], $pastDist."/".$nomeImage["name"]);
  }
  
  
  
  $table = "";
  $t = array();
  $date = array();
  $method = "";
  $data = array();
  
  
  // ############### percorrendo o ojecto(data)=>requisição ###############
  foreach ($dat as $tab => $f) 
  {
      if ($tab != "method" && $tab != "id") 
      {
          $table = $tab;
      }
      
      $method = $dat["method"];
      
      if ($tab != "method" && $tab != "id")
      {
          $data = $f;
      }
  }
  
  $res = array();
  
  //############### Verificando a conexação com o banco de Dados ###############
  $conn = mysqli_connect("localhost", "root", "", "kapay_db");
  
  
  if ($dat["method"] != null) 
  {
      //############### Verificando se o method escolhido pelo usuário de upload ###############
      if ($dat["method"] == "upload") 
      {
          $ext=explode(".",$dat["name"]);
          $arrimagem=array("image"=>array("type"=>"image/".$ext[1],"tmp_name"=>@"C:\wamp64\-tmp\php8FA2.tmp","size"=>$dat["size"],"error"=>0));
          $arrimagem["image"]["name"]=$dat["name"];
          $arrimagem["image"]["tmp_name"]=str_replace("-","",$arrimagem["image"]["tmp_name"]);
          $pastDist = "upload";
          $nomeImage = $arrimagem["image"];
          $pastDist = "upload";
          move_uploaded_file($nomeImage["tmp_name"], $pastDist."/".$nomeImage["name"]);
      }
      
      //############### Medtodo responsavel por adicionar os dados numa tabela ###########
      if ($dat["method"] == "Add") 
      {
          $campo;
          $valuesde;
          foreach ($data as $key => $values) 
          {
              $campo[] = "$key";
              $valuesde[] = "'$values'";
          }
          
          $campo = implode(",", $campo);
          $valuesde = implode(",", $valuesde);
          
          $query = "insert into " . $table . " ($campo) values($valuesde)";
          
          
          if (mysqli_query($conn, $query)) 
          {
              $res = array(
                  "data" => $data,
                  "messagem" => "Sucesso"
              );
          }
          $date = $res;
      }
      
  //############### Medtodo responsavel por fazer a listagem de dados de uma tabela ###########
  
      if ($dat["method"] == "list")
      {
          $tables = array();
          $where = array();
          $field = array();
          $result = mysqli_query($conn, "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'kapay_db' AND TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL");
          
          foreach ($result as $row) 
          {
              $REFERENCED_TABLE_NAME[] = $row["REFERENCED_TABLE_NAME"];
              $TABLE_NAME[] = $row["TABLE_NAME"];
              $where[] = $row["TABLE_NAME"] . "." . $row["COLUMN_NAME"] . "=" .  $row["REFERENCED_TABLE_NAME"] . "." . $row["REFERENCED_COLUMN_NAME"];
              $tables[] = $row["TABLE_NAME"];
              $tables[] = $row["REFERENCED_TABLE_NAME"];
              $table = $row["TABLE_NAME"];
              $tableRefere = $row["REFERENCED_TABLE_NAME"];
              $results = mysqli_query($conn, "show columns from  $table");
              $resultss = mysqli_query($conn, "show columns from  $tableRefere");
              foreach ($results as $fiels)
                  $field[] = $table . "." . $fiels["Field"] . " as " . $fiels["Field"] . "_" . $table;
              foreach ($resultss as $fielss)
                  $field[] = $tableRefere . "." . $fielss["Field"] . " as " . $fielss["Field"] . "_" . $tableRefere;
          }
          $tables = implode(" , ", $tables);
          $where = implode(" and ", $where);
          $field = implode(" , ", $field);
          $resultsdd = mysqli_query($conn, "SELECT {$field} FROM {$tables} where {$where}");
          
          if ($resultsdd == false) 
          {
              
              $query = "select*from  " . $table . "";
              $result = mysqli_query($conn, $query);
              
              foreach ($result as $var) 
              {
                  $t[] = $var;
              }
              
              $var = array_keys($data);
  
              if ($data != null) 
              {
                  for ($n = 0; $n < count($t); $n++) 
                  {
                      foreach ($t[$n] as $rttt => $value) 
                      {
                          if (!in_array($rttt, $var)) 
                          {
                              unset($t[$n][$rttt]);
                          }
                      }
                  }
              }
              $res = array(
                  "data" => $t,
                  "messagem" => "Sucesso"
  
              );
              $date = $res;
          } 
          else 
          {
              foreach ($resultsdd as $var) 
              {
                  $t[] = $var;
              }
                    $vars = array_keys($data);
         
                    if($vars!=null)
                    {
                      for($gg=0;$gg<count($t);$gg++)
                      {
                        foreach($t[$gg] as $key=>$val)
                        {
                            if (!in_array($key,$vars)) 
                            {
                                unset($t[$gg][$key]);
                            }
                        }
                      }
                    }
              $res = array(
                  "data" => $t,
                  "messagem" => "Sucesso"
              );
              $date = $res;
          }
      }
      
      //############### Medtodo responsavel por fazer a pesquisa de dados nas tabelas ###########
      if ($dat["method"] == "search") 
      {
          foreach ($data as $key => $values) 
          {
              $campo[] = "$key='$values'";
          }
          $campo = implode(" or ", $campo);
          $query = "select*from  " .  $table . " where " . $campo . "  ";
          $result = mysqli_query($conn, $query);
          
          foreach ($result as $var) 
          {
              $t[] = $var;
          }
          $res = array(
              "data" => $t,
              "messagem" => "Sucesso"
  
          );
          $date = $res;
      }
      
      
      //############### Medtodo responsavel por eliminar os dados de uma tabela ###########
      if ($dat["method"] == "Delete") {
  
          $query = "delete from  " . $table . " where id" . "=" . $dat["id"] . "  ";
          $result = mysqli_query($conn, $query);
          if ($result) {
              $res = array(
                  "data" => null,
                  "messagem" => "Sucesso"
  
              );
          }
  
          $date = $res;
      }
      
      //############### Medtodo responsavel por editar os dados de uma tabela ###########
      if ($dat["method"]  == "Update") 
      {
  
          foreach ($data as $key => $values) 
          {
              $campo[] = "$key='$values'";
          }
          $campo = implode(" , ", $campo);
          $query = "update  " . $table . " set  " . $campo . "  where Id" . "=" . $dat["id"] . "";
          $result = mysqli_query($conn, $query);
          
          if ($result) 
          {
              $res = array(
                  "data" => $data,
                  "messagem" => "Sucesso"
  
              );
          }
  
          $date = $res;
      }
      
      //############### Mostrar as tabelas da base de dados ###########
      if ($dat["method"]  == "showTable") 
      {
          $datas = array();
          $query = "SHOW Tables";
          $result = mysqli_query($conn, $query);
          if ($result)
           {
              foreach ($result as $row) 
              {
                  $datas[] = $row["Tables_in_kapay_db"];
              }
              $res = array(
                  'data' => $datas,
                  'success'    =>    '1'
              );
          } 
          else 
          {
              $res = array(
                  'data' => null,
                  'success'    =>    '0'
              );
          }
          $date = $res;
      }
      
      //###############Mostrar as propiedades de uma tabela###########
      if ($dat["method"] == "fieldTable") 
      {
          $datas = array();
          $query = "SHOW COLUMNS FROM $table";
          $result = mysqli_query($conn, $query);
          
          if ($result) 
          {
              foreach ($result as $row) 
              {
                  $datas[] = $row["Field"];
              }
              $res = array(
                  'data' => $datas,
                  'success'    =>    '1'
              );
          } 
          else 
          {
              $res = array(
                  'data' => null,
                  'success'    =>    '0'
              );
          }
          $date = $res;
      }
      echo json_encode($date);
  }
?>