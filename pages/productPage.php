<?php
$productId = $_GET['id'] ?? "";
$userId = $_SESSION['id'] ?? "";

if ($productId === "") {
    echo "<h1>No product specified</h1>";
    exit;
}

require_once __DIR__ . "/../bootstrap.php";

try {

    $product = Db::usingDbConnection(function () use ($productId) {
        return Product::viewProduct($productId);
    });

    if (!$product) {
        echo "<h1>This is not a valid product request</h1>";
        exit;
    }

} catch (PDOException $e) {
    Db::handleException($e);
    echo "<h1>Something went wrong. Try again later!</h1>";
    exit;
} catch (Exception $e) {
    echo "<h1>Something went wrong. Try again later!</h1>";
    exit;
}
?>

<main>
    <div class="page">
	<div class = "divblock">
        <div class="page-header">
            <div class="left">
                <h1><?= $product->getTitle()?></h1>
            </div>
        </div>
		<div class="scrollImgs">
            <?php foreach($product->getImgs() as $image):?>
            	<img alt="productImage" class="productImage" src="actions/image.php?id=<?= $image->getImageId()?>">
            <?php endforeach;?>
		</div>
		<img id="nextPic">
        <div class="inputGroup review">
        <?php if($product->getReviews()!==null):?>
        	<details>
            <summary>
                <span>Review</span><span class="rating" style="--rating:<?= $product->getReviews()->getRating()?>;"></span> 
                
            </summary>
                <img alt="reviewImage" src="actions/image.php?id=<?= $product->getReviews()->getPicture()?>">
                <?php foreach($product->getReviews()->getSubRatings() as $subRatings): ?>
                	<br>
                	<span><?= htmlspecialchars($subRatings->getSubReviewCat())?></span>	
                	<div class="rating" style="--rating:<?= (float)$subRatings->getRating()?>;"></div>
                <?php endforeach;?>
                <div> By: <?= createUserSummaryElement($product->getReviews()->getUserSummary())?> </div><br>
                <p><?= htmlspecialchars($product->getReviews()->getComments()) ?> </p>
        </details>
        <?php else:?>
            No review yet
        <?php endif;?>
        </div>
        
        <div class="fields">
        <div class="fieldGroup">
            <label>Price</label>
            <div class="fieldValue">R<?=htmlspecialchars( $product->getPrice())?></div>
        </div>
        <div class="fieldGroup">
            <label>Seller</label>
            <div id="seller" class="fieldValue"><?= createUserSummaryElement($product->getUserSummary()) ?></div>
        </div>
        
        
        
        <div class="fieldGroup">
            <label>State</label>
            <div class="fieldValue"><?= htmlspecialchars($product->getState())?></div>
        </div>
        <div class="fieldGroup">
            <label>Category</label>
            <div class="fieldValue"><?= htmlspecialchars($product->getCategory()->getName())?></div>
        </div>
        
        <div class="fieldGroup">
            <label>Location</label>
            <div class="fieldValue"><?= htmlspecialchars($product->getSuburb()->getCity()->getName())?>,<?= htmlspecialchars($product->getSuburb()->getName())?></div>
        </div>
        
        
        <div class="fieldGroup">
            <label>Description</label>
            <div class="fieldValue"><?= nl2br(htmlspecialchars($product->getDescription()))?></div>
        </div>
        </div>    
        
        <?php if($product->getUserSummary()->getId()===$userId):?>
        	<?php if($product->getState()==='available'):?>
        		<div class="buttonContainer">
        			<button id="btnEdit" onclick="window.location.href='https://myc2c.gamer.gd/index.php?		page=editProduct&id=<?= $product->getId()?>';">Edit</button>
        			<button id="btnDelete">Delete</button>
        		</div>
        	<?php else: ?>
        		<div>Cant be edited when unavailable</div>
			<?php endif; ?>
        <?php elseif($userId): ?>
        	 <?php if($product->getState()==='available'):?>
            	<div class="buttonContainerCenter">
					<button class="wideButton" id='btnSubmit'>Purchase</button>
                </div>
        	<?php else: ?>
        		<div>Product already purchased</div>
        	<?php endif; ?>
        <?php else: ?>
        	<div>Login to perform actions </div>
        <?php endif; ?>
        </div>
	</div>
</main>
<script type="module">
    import {request} from '/pages/apiClient.js';
    import {createUserSummaryElement} from '/pages/createUserSummaryElement.js';
//add back
    
    document.querySelectorAll('.error').forEach(el => {
   		el.classList.add('hidden');
	});
    
	const buttonSubmit = document.getElementById('btnSubmit');
    if(buttonSubmit){
    	buttonSubmit.addEventListener('click', async function(event){
		document.querySelectorAll('.error').forEach(el => {
   			el.classList.add('hidden');
		});
            
        const fd = new FormData();
        fd.append("productId",<?= $product->getId()?>);
        
        const mapToErrorFields={}
        request({url:'actions/reserve.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields})    
	});    
    }
    
    const buttonDelete = document.getElementById('btnDelete');
    if(buttonDelete){
    	buttonDelete.addEventListener('click', async function(event){
		
		alert('Submitted');
        
        const fd = new FormData();
        fd.append("productId",<?= $product->getId()?>);
        
        const mapToErrorFields={}
        const successMessage ="Product deleted";
        const redirect = "https://myc2c.gamer.gd/index.php?page=myListings";
        request({url:'actions/deleteProduct.php',data:fd,method:'POST',mapToErrorFields:mapToErrorFields,successMessage,redirect:redirect})     
	});    
    }
</script>