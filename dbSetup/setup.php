<?php
    try{
        Db::connect();
        $rows = Db::select('SELECT 1 FROM users LIMIT 1');
        return;
	}
	catch(Exception $e){
        $s='a';
    }
	$sql="SET SESSION sql_mode ='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET NAMES utf8mb4;

CREATE TABLE cities(
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	CONSTRAINT uq_cities_name UNIQUE (name)
);

CREATE TABLE suburbs(
	id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    CONSTRAINT fk_suburbs_city_id FOREIGN KEY (city_id) REFERENCES cities(id),
    CONSTRAINT uq_suburbs_city_name UNIQUE (city_id, name)
);

CREATE TABLE images(
	id INT AUTO_INCREMENT PRIMARY KEY,
    raw_data MEDIUMBLOB NOT NULL, 
    mime_type VARCHAR(50) NOT NULL,
    visibility ENUM('public','admin_only') DEFAULT 'public' NOT NULL
);

CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(25) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(10) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    since DATE NOT NULL DEFAULT CURRENT_DATE(),
    password VARCHAR(255) NOT NULL,
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT uq_users_phone UNIQUE (phone)
);

CREATE TABLE categories (
	id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    CONSTRAINT uq_categories_name UNIQUE (name)
);

CREATE TABLE questionares(
	id INT AUTO_INCREMENT PRIMARY KEY,
    state ENUM('active','inactive') DEFAULT 'active'
);

CREATE TABLE bankRecepients(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bank_code INT NOT NULL,
    account_number VARCHAR(20) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    is_default BOOLEAN NOT NULL,
    recipient_code VARCHAR(50) NOT NULL UNIQUE,
    CONSTRAINT fk_bankRecepients_user_id FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE buyerSellerInfo(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
	verified BOOLEAN NOT NULL DEFAULT FALSE,
    banned BOOLEAN NOT NULL DEFAULT FALSE,
    verification_timeOut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    suburb_id INT NOT NULL,
    img_id INT NOT NULL,
    balance DECIMAL(10,2) NOT NULL DEFAULT 1000000.00,
    CONSTRAINT fk_buyerSellerInfo_user_id FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_buyerSellerInfo_suburb_id FOREIGN KEY (suburb_id) REFERENCES suburbs(id),
    CONSTRAINT fk_buyerSellerInfo_img_id FOREIGN KEY (img_id) REFERENCES images(id),
    CONSTRAINT uq_buyerSellerInfo_user_id UNIQUE (user_id)
);

CREATE TABLE products(
	id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    state ENUM('available','unavailable','banned') DEFAULT 'available',
    category_id INT NOT NULL,
    CONSTRAINT fk_products_user_id FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_products_category_id FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE productImgs(
	id INT AUTO_INCREMENT PRIMARY KEY,
    img_id INT NOT NULL,
    product_id INT NOT NULL,
    is_primary BOOLEAN NOT NULL,
    CONSTRAINT fk_productImgs_product_id FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_productImgs_img_id FOREIGN KEY (img_id) REFERENCES images(id)
);

CREATE TABLE productReservations(
	id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    product_price DECIMAL(12,2) NOT NULL,
    reserved_until TIMESTAMP NOT NULL DEFAULT (NOW() + INTERVAL 15 MINUTE),
    state ENUM('active','cancelled','expired','completed') DEFAULT 'active',	
    CONSTRAINT fk_productReservations_user_id FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_productReservations_product_id FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    product_id INT NULL,
    seller_id INT NULL,
    reservation_id INT NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    product_title VARCHAR(100) NOT NULL,
    state ENUM('paid','reversed','received','completed') NOT NULL,
    state_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_orders_product_id FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    CONSTRAINT fk_orders_seller_id FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_orders_reservation_id FOREIGN KEY (reservation_id) REFERENCES productReservations(id)
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    order_id INT NULL,
    affected_user_id INT NOT NULL,
    type ENUM('payment', 'refund', 'payout','timeout_refund') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    reference VARCHAR(100) UNIQUE,
    paystack_refund_id BIGINT UNSIGNED,
    related_transaction_id BIGINT UNSIGNED,
    recepient_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_reservation_id FOREIGN KEY (reservation_id) REFERENCES productReservations(id),
    CONSTRAINT fk_transactions_order_id FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT fk_transactions_affected_user_id FOREIGN KEY (affected_user_id) REFERENCES users(id),
    CONSTRAINT fk_transactions_recepient_code FOREIGN KEY (recepient_code) REFERENCES bankRecepients(recipient_code)
);

CREATE TABLE reviews(
	id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    img_id INT NOT NULL,
    comments TEXT NOT NULL,
    CONSTRAINT fk_reviews_product_id FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_reviews_user_id FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_reviews_img_id FOREIGN KEY (img_id) REFERENCES images(id),
    CONSTRAINT uq_reviews_user_product UNIQUE (user_id, product_id)
);

CREATE TABLE subRatings(
	id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    rating INT NOT NULL,
    sub_review_cat ENUM('Communication','Condition') NOT NULL,
    CONSTRAINT fk_subRatings_review_id FOREIGN KEY (review_id) REFERENCES reviews(id)
);

CREATE TABLE questionareQuestions(
	id INT AUTO_INCREMENT PRIMARY KEY,
    questionare_id INT NOT NULL,
    question VARCHAR(255) NOT NULL,
    CONSTRAINT fk_questionareQuestions_questionare_id FOREIGN KEY (questionare_id) REFERENCES questionares(id)
);

CREATE TABLE questionareChoices(
	id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    choice VARCHAR(255),
    is_correct BOOLEAN,
    CONSTRAINT fk_questionareChoices_question_id FOREIGN KEY (question_id) REFERENCES questionareQuestions(id)
);

CREATE TABLE questionareResponses(
	id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    questionare_choice_id INT NOT NULL,
    CONSTRAINT fk_questionareResponses_user_id FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_questionareResponses_questionare_choice_id FOREIGN KEY (questionare_choice_id) REFERENCES questionareChoices(id),
    CONSTRAINT uq_questionareResponses_user_choice UNIQUE (user_id, questionare_choice_id)
);

CREATE TABLE questionareUserAttempt(
	id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    questionare_id INT NOT NULL,
    CONSTRAINT fk_questionareUserAttempt_user_id FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_questionareUserAttempt_questionare_id FOREIGN KEY (questionare_id) REFERENCES questionares(id)
);

CREATE TABLE verificationRequest(
	id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    img_id_card_id INT NOT NULL,
    img_selfie_id INT NOT NULL,
    CONSTRAINT fk_verificationRequest_user_id FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_verificationRequest_img_id_card_id FOREIGN KEY (img_id_card_id) REFERENCES images(id),
    CONSTRAINT fk_verificationRequest_img_selfie_id FOREIGN KEY (img_selfie_id) REFERENCES images(id)
);

CREATE TABLE authLog(
	id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    role ENUM('user','admin') NOT NULL,
    action VARCHAR(255) NOT NULL,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_authLog_user_id FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE userLogs(
	id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('user','admin') NOT NULL,
    action VARCHAR(255) NOT NULL,
    target_type VARCHAR(255) NOT NULL,
    target_id INT NOT NULL,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_userLogs_user_id FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO cities (name) VALUES ('Pretoria');
SET @cityId = LAST_INSERT_ID();
INSERT INTO suburbs (name,city_id) VALUES ('Brooklyn',@cityId),('Hatfield',@cityId),('Arcadia',@cityId);


INSERT INTO cities (name) VALUES ('Johannesburg');
SET @cityId = LAST_INSERT_ID();
INSERT INTO suburbs (name,city_id) VALUES ('Sandton',@cityId),('Rosebank',@cityId),('Randburg',@cityId);

INSERT INTO categories (name) VALUES  ('Technology'),('Vechiles'),('Houses'),('Furnature'),('Appliances'),('Clothes'),('Entertainment'),('Music');
-- 1. Create a questionnaire
INSERT INTO questionares (state) VALUES ('active');
SET @questionareId = LAST_INSERT_ID();


-- Question 1
INSERT INTO questionareQuestions (questionare_id, question) 
VALUES (@questionareId, 'How should you verify a seller before paying?');
SET @questionId = LAST_INSERT_ID();

INSERT INTO questionareChoices (question_id, choice, is_correct) VALUES
(@questionId, 'Trust the seller based on their profile picture', false),
(@questionId, 'Only read reviews and ratings from previous buyers', true),
(@questionId, 'Ask the seller to meet immediately without checking anything', false),
(@questionId, 'Pay via external channels before using the platform', false);


-- Question 2
INSERT INTO questionareQuestions (questionare_id, question) 
VALUES (@questionareId, 'How should you meet a seller for a transaction?');
SET @questionId = LAST_INSERT_ID();

INSERT INTO questionareChoices (question_id, choice, is_correct) VALUES
(@questionId, 'At the seller’s home', false),
(@questionId, 'In a public place, preferably with people around', true),
(@questionId, 'In a secluded area to avoid crowds', false),
(@questionId, 'Only virtually, never meet in person', false);


-- Question 3
INSERT INTO questionareQuestions (questionare_id, question) 
VALUES (@questionareId, 'What is the safest way to pay for items on the platform?');
SET @questionId = LAST_INSERT_ID();

INSERT INTO questionareChoices (question_id, choice, is_correct) VALUES
(@questionId, 'Direct bank transfer to the seller', false),
(@questionId, 'Cash in hand before inspecting the item', false),
(@questionId, 'Use the platform’s escrow system', true),
(@questionId, 'Pay via random third-party apps outside the platform', false);


-- Question 4
INSERT INTO questionareQuestions (questionare_id, question) 
VALUES (@questionareId, 'What should you do before meeting a seller in person?');
SET @questionId = LAST_INSERT_ID();

INSERT INTO questionareChoices (question_id, choice, is_correct) VALUES
(@questionId, 'Share your personal address and wait for them to come to you', false),
(@questionId, 'Let a friend or family know where you’re going', true),
(@questionId, 'Ignore safety and go alone', false),
(@questionId, 'Meet at night in a poorly lit area', false);
";
	try{
        Db::connect();
        Db::insert($sql,[]);
        
       $image = Image::prepareImage(__DIR__."/../images/default_profile.jpg",500,500,80);
        $image->setVisibility('public');
        $image->save();
        
        $user1 = new User();
		$user1->setUsername('vian');
		$user1->setPhone('0646843349');
		$user1->setEmail('vian.dup@icloud.com');
		$user1->setPassword('Viandp2@');
		$user1->setRole('user');
        $user1->save();
        
       
        
        $buyerSellerInfo1=new BuyerSellerInfo();
        $buyerSellerInfo1->setUserId($user1->getId());
        $buyerSellerInfo1->setSuburbId(1);
        $buyerSellerInfo1->setImageId(1);
        $buyerSellerInfo1->save();
        
         $user1->updateVerified(1);
        
		$attempt = new QuestionareAttempt();
        $attempt->setUserId(1);
        $attempt->setQuestionareId(1);
        $attempt->save();
        
        
        for($i=0;$i <=3;$i++){
            $response = new QuestionareResponse(1,1+$i*4);
        	$response->save();
        }
        
        
        $user2 = new User();
        $user2->setUsername('vian2');
		$user2->setPhone('0646843348');
		$user2->setEmail('eduv4877254@vossie.net');
		$user2->setPassword('Viandp2@');
		$user2->setRole('user');
        $user2->save();
        
        $buyerSellerInfo2=new BuyerSellerInfo();
        $buyerSellerInfo2->setUserId($user2->getId());
        $buyerSellerInfo2->setSuburbId(4);
        $buyerSellerInfo2->setImageId(1);
        $buyerSellerInfo2->save();
        
        $attempt2 = new QuestionareAttempt();
        $attempt2->setUserId(2);
        $attempt2->setQuestionareId(1);
        $attempt2->save();
        
        for($i=0;$i <=3;$i++){
            $response = new QuestionareResponse(2,1+$i*4);
        	$response->save();
        }
		
        $user3 = new User();
        $user3->setUsername('vian3');
		$user3->setPhone('0646843347');
		$user3->setEmail('admin1@gmail.com');
		$user3->setPassword('Viandp2@');
		$user3->setRole('admin');
        $user3->save();
        
        $user4 = new User();
        $user4->setUsername('vian4');
		$user4->setPhone('0646843346');
		$user4->setEmail('vian2.dup@icloud.com');
		$user4->setPassword('Viandp2@');
		$user4->setRole('user');
        $user4->save();
        
        $buyerSellerInfo3=new BuyerSellerInfo();
        $buyerSellerInfo3->setUserId($user4->getId());
        $buyerSellerInfo3->setSuburbId(4);
        $buyerSellerInfo3->setImageId(1);
        $buyerSellerInfo3->save();
        
        $user4->updateBanned(1);
    }
	catch(Exception $e){
    	echo getJsonFailure($e);   
    }
?>