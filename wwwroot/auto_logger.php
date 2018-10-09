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

#Server Parameters
$servername = "<server>";
$username = "<username of DB>";
$password = "<password>";
$dbname = "<racktables DB name>";

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
