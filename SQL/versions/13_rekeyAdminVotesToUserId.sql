ALTER TABLE admin_vote
ADD COLUMN vote_voter_user_id INT NULL AFTER vote_voter_username,
ADD COLUMN vote_subject_user_id INT NULL AFTER vote_subject_username;

UPDATE admin_vote
SET vote_voter_user_id = (SELECT u.user_id FROM user u WHERE u.user_username = vote_voter_username);

UPDATE admin_vote
SET vote_subject_user_id = (SELECT u.user_id FROM user u WHERE u.user_username = vote_subject_username);

ALTER TABLE admin_vote
DROP COLUMN vote_subject_username,
DROP COLUMN vote_voter_username;