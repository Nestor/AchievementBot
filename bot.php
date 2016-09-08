<?PHP
/*-------SETTINGS-------*/
$ts3_ip = '127.0.0.1';
$ts3_queryport = 10011;
$ts3_user = 'serveradmin';
$ts3_pass = '';
$ts3_port = 9987;
$ts3_name = 'AchievementBot';

//You can add multiple achievements by increasing the value of the array
$achievement[0]['usersOnline'] = 2; //The amount of users that have to be online in order to receive the award
$achievement[0]['groupID'] = 23; //The groupID that should be assigned
$achievement[0]['message'] = ""; //Text that the user will receive, keep empty to disable the message
$achievement[0]['keepGroup'] = false; //Should the user keep the group after the amount of onlineusers is below the required amount?
/*----------------------*/
require("ts3admin.class.php");

$tsAdmin = new ts3admin($ts3_ip, $ts3_queryport);

if($tsAdmin->getElement('success', $tsAdmin->connect())) {
	$tsAdmin->login($ts3_user, $ts3_pass);
	$tsAdmin->selectServer($ts3_port);
  $tsAdmin->setName($ts3_name);

	while(true){
		$clients = $tsAdmin->clientList("-groups");
		foreach($achievement as $achieve){
			if(count($clients['data']) - 1 >= $achieve['usersOnline']){
				foreach($clients['data'] as $client) {
					$serverGroups = explode(",", $client['client_servergroups']);
					$needsGroup = true;
					foreach($serverGroups as $serverGroup){
						if($serverGroup == $achieve['groupID']){
							$needsGroup = false;
						}
					}
					if($needsGroup == true && $client['client_type'] == 0){
						$tsAdmin->serverGroupAddClient($achieve['groupID'], $client['client_database_id']);
						if(!empty($achieve['message'])){
                            $tsAdmin->sendMessage(1, $client['clid'], $achieve['message']);
                        }
						echo $client['client_nickname']." was added to server group serverGroup(".$achieve['groupID'].")\n";
					}
				}
			} else if(!$achieve['keepGroup']) {
                foreach($clients['data'] as $client) {
                    $hasGroup = false;
                    $serverGroups = explode(",", $client['client_servergroups']);
					foreach($serverGroups as $serverGroup){
						if($serverGroup == $achieve['groupID']){
							$hasGroup = true;
						}
					}
                    if($hasGroup == true && $client['client_type'] == 0){
                        $tsAdmin->serverGroupDeleteClient($achieve['groupID'], $client['client_database_id']);
                        echo $client['client_nickname']." was removed from server group serverGroup(".$achieve['groupID'].")\n";
                    }
                }
            }
		}
	}
}else{
	echo 'Connection could not be established.\n';
}

if(count($tsAdmin->getDebugLog()) > 0) {
	foreach($tsAdmin->getDebugLog() as $logEntry) {
		echo $logEntry.'\n';
	}
}
?>
