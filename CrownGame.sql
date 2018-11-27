drop database  CrownGame;
create database CrownGame;
use CrownGame;
CREATE TABLE  login (
	 userID 	INTEGER PRIMARY KEY AUTO_INCREMENT,
	 userUID VARCHAR(36),
	 userName 	varchar(25),
	 password 	varchar(200),
	LastActiveDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Remove Trigger because isnot supported by all server
--CREATE TRIGGER before_insert_login
--BEFORE INSERT ON login
--FOR EACH ROW
--	SET new.userUID = uuid();

DESCRIBE login;

insert into login(userName,password)values ('a','a');
insert into login(userName,password)values ('b','b');
select * from login;

CREATE TABLE  playRequests (
  gameSessionUID VARCHAR(36),
	playerFromUID VARCHAR(36),
	playerToUID VARCHAR(36),
	requestStatus VARCHAR(10) default  'pending',
  requestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  playerFromLastActiveDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  playerToLastActiveDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  numberOfActivePlayer int  DEFAULT 1
);

--CREATE TRIGGER before_insert_playRequests
--BEFORE INSERT ON playRequests
--FOR EACH ROW
--	SET new.gameSessionUID = uuid();


DESCRIBE playRequests;

CREATE TABLE  gameSession (
  gameSessionUID VARCHAR(36),
	playerUID VARCHAR(36),
	playerLocation VARCHAR(36),
  moveDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  diceValue int
);

DESCRIBE gameSession;


CREATE TABLE  chating (
  gameSessionUID VARCHAR(36),
	chatText 	varchar(1000),
	chatTextDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
DESCRIBE chating;

