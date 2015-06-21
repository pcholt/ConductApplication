<html>
<head>
	<title></title>
</head>
<body>

<?php
require_once("config.php");
require_once("PreparedStatements.class.php");

/**
	Body of form processor.
	1. Create DTO access to database
	2. Add a lead using the form $_POST data
	3. Send an email if an agent was found
*/

try {

	$db = new PDO("mysql:dbname=".DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
	$dto = new PreparedStatements($db);

	$lead_id = $dto->add_lead($_POST);

	if ($lead_id) {
		$lead = $db->query(
			"select * from leads where id="
				.$db->quote($lead_id)
			)->fetch(PDO::FETCH_OBJ);
		$agent = $db->query(
			"select agents.* from agents, leads where leads.id="
				.$db->quote($lead_id)." and agents.id=agent_id"
			)->fetch(PDO::FETCH_OBJ);
		send_message($lead, $agent);
	}

}
catch (PDOException $e) {
	echo "Connection failure" . $e->getMessage();
	die();
}



/**

	Send the message created by the given POST message and allocated to the
	given agent.

	$lead:
		row containing the new lead
	$agent:
		row containing the agent

*/

function send_message($lead, $agent)
{
	echo("Send to ".$agent->email." and ".$lead->email);
	echo ("\n");

	// UNCOMMENT THESE LINES TO SEND ACTUAL EMAILS:
	//  mail($agent->email, "Lead created", $lead->message);
	//  mail($lead->email, "Confirm", $lead->message);

}

?>
	<form action="form_processor.php" method="POST">
		<table>
		<tr><td>first_name:</td><td> <input name="first_name"></td></tr>
		<tr><td>last_name:</td><td> <input name="last_name"></td></tr>
		<tr><td>email:</td><td> <input name="email"></td></tr>
		<tr><td>mobile:</td><td> <input name="mobile"></td></tr>
		<tr><td>message:</td><td> <input name="message"></td></tr>
		<tr><td></td><td> <input type="submit"/></td></tr>
		<table>
	</form>
</body>
</html>
