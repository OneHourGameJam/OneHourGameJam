<?php

if(IsAdmin()){
    $voteSubjectUsername = $_POST["adminVoteSubjectUsername"];
    $voteType = $_POST["adminVoteType"];
    CastVoteForAdmin($voteSubjectUsername, $voteType);
}

?>