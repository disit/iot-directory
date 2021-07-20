<?php
/* Snap4City: IoT-Directory
   Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. */

$result=array("status"=>"","msg"=>"","content"=>"","log"=>"", "error_msg"=>"");

/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)
result["log"] keep trace of the operations executed on the db

This array should be encoded in json
*/

header("Content-type: application/json");
header("Access-Control-Allow-Origin: *\r\n");
include ('../config.php');
include ('common.php');
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);

error_reporting(E_ERROR | E_NOTICE);

if(!$link->set_charset("utf8"))
{
        exit();
}

if(isset($_REQUEST['action']) && $_REQUEST['action']=='deploy' && isset($_REQUEST['ipaddr'])) {
    $ipaddr = mysqli_real_escape_string($link, $_REQUEST['ipaddr']); //TBD check 
    $query = "SELECT * FROM orionbrokers WHERE status = 'deploy' AND ipaddr = '$ipaddr' LIMIT 1";
    $res = mysqli_query($link, $query) or die(mysqli_error($link));
    if ($res) {
        if ($row = mysqli_fetch_assoc($res)) {
            $id = $row['id_orionbroker'];
            $name = $row['name'];
            $ext_port=$row['external_port'];
            $access_port=$row['access_port'];
            $organization = $row['organization'];
            $orion_image = $row['orion_image'];
            $multitenacy =$row['multitenacy'];
            mysqli_query($link, "UPDATE orionbrokers SET status='deploying' WHERE id_orionbroker=$id") or die(mysqli_error($link));
            echo <<<EOT
NAME=$name
if [ ! -d "\$NAME" ] 
then
  echo copy dir for \$NAME
  cp -r template \$NAME
fi

cat << EOF > \$NAME/.env
NAME=\$NAME
EXT_PORT=$ext_port
ORGANIZATION=$organization
ACCESS_PORT=$access_port
ORION_IMAGE=$orion_image
MULTITENACY=$multitenacy
EOF

cd \$NAME
docker-compose up -d

while ! nc -z $ipaddr $access_port; do   
  echo wait port $ipaddr:$access_port
  sleep 1
done

while ! nc -z $ipaddr $ext_port; do   
  echo wait port $ipaddr:$ext_port
  sleep 1
done

curl "$appUrl/api/orion-deploy.php?action=deployed&id=$id"

EOT;
        }
    }
} else if($_REQUEST['action']=='deployed' && isset($_REQUEST['id'])) {
    $id = mysqli_real_escape_string($link, $_REQUEST['id']);
    mysqli_query($link, "UPDATE orionbrokers SET status='deployed' WHERE id_orionbroker='$id'") or die(mysqli_error($link));
} else if($_REQUEST['action']=='nginx_proxy') {
    $query = "SELECT * FROM orionbrokers";
    $res = mysqli_query($link, $query) or die(mysqli_error($link));
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $id = $row['id_orionbroker'];
            $name = $row['name'];
            $ipaddr = $row['ipaddr'];
            $ext_port=$row['external_port'];
            $access_port=$row['access_port'];
            $organization = $row['organization'];
            echo <<< EOT
location  /$name/v2 {
  proxy_set_header Host \$http_host;
  proxy_set_header X-Real-IP \$remote_addr;
  proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
  proxy_set_header X-Forwarded-Proto \$scheme;
  proxy_http_version 1.1;
  proxy_connect_timeout 60;
  proxy_send_timeout 60;
  proxy_read_timeout 60;
  send_timeout 60;
  proxy_pass "https://$ipaddr:$access_port/v2";
}


EOT;
        }
    }

}
