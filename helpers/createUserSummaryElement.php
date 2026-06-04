<?php
    function createUserSummaryElement($userSummary) {
    echo "<a href='index.php?page=viewProfile&id=" . $userSummary->getId() . "'>
        <span>" . $userSummary->getUsername() . "</span>"
        .// only show if verified
        ($userSummary->getVerified() === 1 ? "<span class='verified'>✔</span>" : "")  
        .//only show if banned
        ($userSummary->getBanned() === 1 ? "<span class='banned'>X</span>" : "")  ."
        <span>" . $userSummary->getAverageReviews() . "</span>
        <span>(" . $userSummary->getTotalReviews() . " reviews)</span>
        <div class='rating' style='--rating:" . $userSummary->getAverageReviews() . "'></div>" .


    "</a>";
}
?>