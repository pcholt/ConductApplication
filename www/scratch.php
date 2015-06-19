<?php
require_once("config.php");
require_once("PreparedStatements.class.php");

/**
	Body of form processor.
	1. Create DTO access to database
	2. Add a lead using the form $POST data
	3. Send an email if an agent was found
*/

try {

	$db = new PDO("mysql:dbname=".DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
	$dto = new PreparedStatements($db);

	$lead_id = $dto->add_lead($POST);

	if ($lead_id) {
		$lead = $db->query("select * from leads where id=".$db->quote($lead_id));
		$agent = $db->query("select agents.* from agents, leads where leads.id=".$db->quote($lead_id)." and agents.id=agent_id");
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
// mail($agent->email, "Lead created", $POST['message']);
// mail($POST['email'], "Confirm", $POST['message']);
}