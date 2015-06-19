<?php
	define("SEMAPHORE_ID", 99211283973);
/**

	Data Access Object for querying and inserting into the agents/leads database.

*/

	class PreparedStatements {

		// Find the active agent with the lowest number of leads.
		public $find_agent_with_fewest_leads;

		// Add a lead record, assigned to an agent_id.
		public $lead_insert;

		private $db;

		private $semaphore;

		/**
		Construct the prepared statements using
		the given PDO database
		*/
		public function __construct($db) {

			$this->find_agent_with_fewest_leads = $db->prepare("
				select * from agents 
				where active=1 
				order by (
					select count(*)
					from leads 
					where leads.agent_id = agents.id
					) asc limit 1;
			");

			$this->lead_insert = $db->prepare("
				insert into leads (
					agent_id, first_name, last_name, email,
					mobile, message, created, modified
					) values(
					:agent_id, :first_name, :last_name, :email,
					:mobile, :message, NOW(), NOW()
					);
			");

			$this->db = $db;

			$this->semaphore = sem_get(SEMAPHORE_ID, 1, 0666, 1);
		}

		/**
		Create a new lead given an unquoted and unprepared array()
		from a lead-creation form, and return the id of the new lead record.
		Return false if there are no active agents able to take the lead.
		*/
		public function add_lead($post_unquoted) {

			sem_acquire($this->semaphore);
			$this->db->beginTransaction();

			$this->find_agent_with_fewest_leads->execute();
			$agent_exists = ($this->find_agent_with_fewest_leads->rowCount() == 1);

			if ($agent_exists) {

				$agent = $this->find_agent_with_fewest_leads->fetch(PDO::FETCH_OBJ);

				// Uncomment the following line in order to test simultaneous access.
				// sleep(5);

				$this->lead_insert->execute(array(
					":agent_id"=>$agent->id,
					":first_name"=>$post_unquoted['first_name'],
					":last_name"=>$post_unquoted['last_name'],
					":email"=>$post_unquoted['email'],
					":mobile"=>$post_unquoted['mobile'],
					":message"=>$post_unquoted['message'],
					));

				$lead_id = $this->db->lastInsertId();
			}
			else {
				$lead_id = FALSE;
			}

			$this->db->commit();
			sem_release($this->semaphore);

			return $lead_id;

		}


	}