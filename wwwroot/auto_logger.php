<?php
# Copyright (c) 2017, Scott Groel, Clemson University, United States
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification,
# are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this list of conditions
# and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions
# and the following disclaimer in the documentation and/or other materials provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
# WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
# PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
# ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
# HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
# OF THE POSSIBILITY OF SUCH DAMAGE.

#
# README: To configure this application, please configure the config.ini.php file located by default in
# the autologger directory. This directory can be moved outside of the root web directory for security
# purposes. Please ensure the correct unix permissions and owner are applied. It is recommended to use
# 440 for permissions once configured. If relocating the config.ini.php file from the default directory,
# it is required that you update the path of the file. This can be done by updating the path in the 
# variable $config_file. If intending to leave config.ini.php in its default location, additional
# protection should be used such as preventing access to this file by using .htaccess. By default, the
# file is protected by wrapping the contents in php which by default, just terminates when loaded.
#



#Read in configuration from file, UPDATE THIS IF MOVING THE CONFIG FROM DEFAULT LOCATION
$srvconfig = parse_ini_file('autologger/config.ini.php');

#Server Parameters
$servername = $srvconfig['server'];
$username = $srvconfig['username'];
$password = $srvconfig['password'];
$dbname = $srvconfig['dbname'];

#node = target node to add note to
#note = note that is to be attributed to the note
$obj_name = $_GET['node'];
$note = $_GET['note'];
$problem = $_GET['prob'];

try {
    #Create connection to SQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    #Relate nodename to objectname
    $stmt = $conn->prepare("SELECT Object.id FROM Object WHERE Object.name='$obj_name'"); 
    $stmt->execute();
    $result = $stmt->Fetch (PDO::FETCH_ASSOC);

    #Insert this entry into the logs
    $sql = "INSERT INTO ObjectLog (id, object_id, user, date, content)
        VALUES (NULL, '$result[id]', 'autologger', NOW(), '$note')";
    $conn->exec($sql);

    #Mark if there is a problem with the node, yes for problem, no for normal"
    $sql = "UPDATE Object SET has_problems='$problem' WHERE id='$result[id]'";
    $conn->exec($sql);

    #Get Rack of node to regenerate thumbnail
    $stmt = $conn->prepare("SELECT RackSpace.rack_id FROM RackSpace WHERE RackSpace.object_id='$result[id]' LIMIT 1");
    $stmt->execute();
    $result = $stmt->Fetch (PDO::FETCH_ASSOC);

    #Clear Rack Thumbnail Data. Will be rebuilt on next page call
    $sql = "UPDATE RackThumbnail SET thumb_data=NULL WHERE rack_id='$result[rack_id]'";
    $conn->exec($sql);
}
catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
?>
