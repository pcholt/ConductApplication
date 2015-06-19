# ConductApplication
The Conduct Logic Question requires a simple application with a single form and a form processor. The form processor takes the data from the form, manipulates the database, and creates either two or zero emails.

## Core logic

### "Simultaneous submissions will occur"

I tried to use a database lock on table read, then transactions, then select as a subquery to the INSERT statement (to make the database access a single statement), but I couldn't handle simultaneous access.  I ended up using semaphores, as you can see.  I explicity set the `$autorelease` flag to release the semaphore if a process terminates.

### SQL

My first attempt at a solution was tested using a standalone script containing a proof-of-concept for the database queries which appear in the solution.

	SELECT * from agents 
	WHERE active=1 
	ORDER BY (
		SELECT COUNT(*)
		FROM leads 
		WHERE leads.agent_id = agents.id
	) ASC LIMIT 1;

This query searches for active agents, sorted by the number of leads that the agent has allocated against them.  Sorting in ascending order and only taking the first row results in retrieval of the `agents` record with the fewest number of associated `leads` records.

## Installation

1. Copy all files from the `www` directory into the web directory you want to host the application
2. Load the sql data into the database from `initial.sql` with something like `cat initial.sql | mysql -ufoo -p conduct_logic_question`
3. Create `www/config.php` in `www`. `www/config_default.php` is in the correct format.


## Afterthoughts

An interesting challenge. Multiple options for a solution to the atomic-update question present themselves, and I had to try them all out before settling on semaphores.  I thought this could be solved with a little clever SQL, but experimentation and continuous testing showed me the way.