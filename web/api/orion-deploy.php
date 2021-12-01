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

header("Content-type: application/json");
header("Access-Control-Allow-Origin: *\r\n");
include ('../config.php');
include ('common.php');
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);

error_reporting(E_ERROR | E_NOTICE);

if (!$link->set_charset("utf8")) {
  exit();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'deploy' && isset($_REQUEST['ipaddr'])) {
  $ipaddr = mysqli_real_escape_string($link, $_REQUEST['ipaddr']); //TBD check 
  $query = "SELECT * FROM orionbrokers WHERE status IN ('deploy','upgrade','delete') AND ipaddr = '$ipaddr' LIMIT 1";
  $res = mysqli_query($link, $query) or die(mysqli_error($link));
  if ($res) {
    if ($row = mysqli_fetch_assoc($res)) {
      $id = $row['id_orionbroker'];
      $name = $row['name'];
      $ext_port = $row['external_port'];
      $access_port = $row['access_port'];
      $organization = $row['organization'];
      $orion_image = $row['orion_image'];
      $multitenacy = $row['multitenacy'];
      $status = $row['status'];
      $new_status = ($status == 'deploy' ? 'deploying' : ($status == 'upgrade' ? 'upgrading' : 'deleting'));
      mysqli_query($link, "UPDATE orionbrokers SET status='$new_status', status_timestamp=NOW() WHERE id_orionbroker=$id") or die(mysqli_error($link));
      switch ($status) {
        case 'deploy':
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

MAX=120
CNT=0
while ! nc -z $ipaddr $access_port ; do   
  if [ \$CNT -ge \$MAX ]; then
    echo "FAILED $ipaddr:$access_port check"
    echo "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-orion-check&id=$id"
    curl -Ss "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-orion-check&id=$id"
    exit;
  fi
  echo wait \$CNT/\$MAX port $ipaddr:$access_port
  sleep 1
  ((CNT++))
done

CNT=0
while ! nc -z $ipaddr $ext_port; do   
  if [ \$CNT -ge \$MAX ]; then
    echo "FAILED $ipaddr:$ext_port check"
    echo "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-filter-check&id=$id"
    curl -Ss "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-filter-check&id=$id"
    exit;
  fi

  echo wait \$CNT/\$MAX port $ipaddr:$ext_port
  sleep 1
  ((CNT++))
done

echo "$redirectUri/api/orion-deploy.php?action=set_status&status=deployed&id=$id"
curl -Ss "$redirectUri/api/orion-deploy.php?action=set_status&status=deployed&id=$id"

EOT;
          break;
        case 'upgrade':
          echo <<<EOT
NAME=$name
if [ ! -d "\$NAME" ]
then
  exit
fi
cd \$NAME
docker pull $orion_image
docker-compose up -d

MAX=120
CNT=0
while ! nc -z $ipaddr $access_port; do
  if [ \$CNT -ge \$MAX ]; then
    echo "FAILED $ipaddr:$access_port check"
    echo "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-orion-check&id=$id"
    curl -Ss "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-orion-check&id=$id"
    exit;
  fi

  echo wait \$CNT/\$MAX port $ipaddr:$access_port
  sleep 1
  ((CNT++))
done

CNT=0
while ! nc -z $ipaddr $ext_port; do
  if [ \$CNT -ge \$MAX ]; then
    echo "FAILED $ipaddr:$ext_port check"
    echo "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-filter-check&id=$id"
    curl -Ss "$redirectUri/api/orion-deploy.php?action=set_status&status=failed-filter-check&id=$id"
    exit;
  fi

  echo wait \$CNT/\$MAX port $ipaddr:$ext_port
  sleep 1
done

echo "$redirectUri/api/orion-deploy.php?action=set_status&status=upgraded&id=$id"
curl -Ss "$redirectUri/api/orion-deploy.php?action=set_status&status=upgraded&id=$id"

EOT;

          break;
        case 'delete':
          echo <<<EOT
NAME=$name
cd \$NAME
docker-compose down 

echo "$redirectUri/api/orion-deploy.php?action=set_status&status=deleted&id=$id"
curl -Ss "$redirectUri/api/orion-deploy.php?action=set_status&status=deleted&id=$id"

EOT;

          break;
      }
    }
  }
} else if ($_REQUEST['action'] == 'set_status' && isset($_REQUEST['id']) && isset($_REQUEST['status'])) {
  $id = mysqli_real_escape_string($link, $_REQUEST['id']);
  $newstatus = mysqli_real_escape_string($link, $_REQUEST['status']);
  if(in_array($newstatus, array('deployed','upgraded','deleted','failed-orion-check','failed-filter-check'))) {
    mysqli_query($link, "UPDATE orionbrokers SET status='$newstatus', status_timestamp=NOW() WHERE id_orionbroker='$id'") or die(mysqli_error($link));
  }
} else if ($_REQUEST['action'] == 'nginx_proxy') {
  $query = "SELECT * FROM orionbrokers where status in ('deployed','upgraded')";
  $res = mysqli_query($link, $query) or die(mysqli_error($link));
  if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
      $id = $row['id_orionbroker'];
      $name = $row['name'];
      $ipaddr = $row['ipaddr'];
      $ext_port = $row['external_port'];
      $access_port = $row['access_port'];
      $organization = $row['organization'];
      echo <<< EOT
location  /orionfilter/$name/v2 {
  proxy_set_header Host \$http_host;
  proxy_set_header X-Real-IP \$remote_addr;
  proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
  proxy_set_header X-Forwarded-Proto \$scheme;
  proxy_http_version 1.1;
  proxy_connect_timeout 60;
  proxy_send_timeout 60;
  proxy_read_timeout 60;
  send_timeout 60;
  proxy_pass "https://$ipaddr:$access_port/orionbrokerfilter/v2";
}
location  /orionfilter/$name/v1 {
  proxy_set_header Host \$http_host;
  proxy_set_header X-Real-IP \$remote_addr;
  proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
  proxy_set_header X-Forwarded-Proto \$scheme;
  proxy_http_version 1.1;
  proxy_connect_timeout 60;
  proxy_send_timeout 60;
  proxy_read_timeout 60;
  send_timeout 60;
  proxy_pass "https://$ipaddr:$access_port/orionbrokerfilter/v1";
}

EOT;
      if($row['enable_direct_access']) {
      echo <<< EOT
location  /orion/$name/ {
  proxy_set_header Host \$http_host;
  proxy_set_header X-Real-IP \$remote_addr;
  proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
  proxy_set_header X-Forwarded-Proto \$scheme;
  proxy_http_version 1.1;
  proxy_connect_timeout 60;
  proxy_send_timeout 60;
  proxy_read_timeout 60;
  send_timeout 60;
  proxy_pass "http://$ipaddr:$ext_port/";
}

EOT;
      }

    }
  }
}
