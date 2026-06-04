<?php
require_once __DIR__ . "/../bootstrap.php";

$userId = $_GET['id'] ?? null;

if (!$userId) {
    echo "<h1>No user selected!</h1>";
    exit;
}

try {

    $user = Db::usingDbConnection(function () use ($userId) {
        return User::withBuyerSellerInfo($userId);
    });

    $userSummary = Db::usingDbConnection(function () use ($userId) {
        return UserSummary::find($userId);
    });

} catch (PDOException $e) {
    Db::handleException($e);
    echo "<h1>Something went wrong when trying to retrieve your data. Try again later!</h1>";
    exit;
} catch (Exception $e) {
    echo "<h1>Something went wrong when trying to retrieve your data. Try again later!</h1>";
    exit;
}
?>

<main>
	<div class="formContainer">
		<h1><?= createUserSummaryElement($userSummary) ?> <span class="profileImage imageBox">
				<img src="actions/image.php?id=<?= $user->getBuyerSellerInfo()->getImageId()?>" id="profileImg">
                </span></h1>

		<div id="profileView">
            <div class="fields">
			<div class="fieldGroup">
				<label>Location</label>
				<div id="cityDisplay" class="fieldValue"><?= htmlspecialchars($user->getBuyerSellerInfo()->getSuburb()->getCity()->getName())?>,<?=htmlspecialchars( $user->getBuyerSellerInfo()->getSuburb()->getName())?></div>
			</div>

			<div class="fieldGroup">
				<label>Phone Number</label>
				<div class="fieldValue"><?= htmlspecialchars($user->getPhone()) ?></div>
			</div>

			<div class="inputGroup">
				<label>Email</label>
				<div class="fieldValue"><?= htmlspecialchars($user->getEmail()) ?></div>
			</div>
            </div>
            <a href="https://myc2c.gamer.gd/index.php?page=market&userId=<?=$user->getId() ?>">View other listings</a>
		</div>

	</div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
   
</script>
