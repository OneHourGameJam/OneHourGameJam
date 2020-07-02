ALTER TABLE poll_vote
ADD COLUMN vote_user_id INT NULL AFTER vote_username;

UPDATE poll_vote
SET vote_user_id = (SELECT user_id FROM user WHERE user_username = vote_username);

ALTER TABLE poll_vote
DROP COLUMN vote_username;