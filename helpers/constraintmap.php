<?php
class ConstraintMap
{
    public const MAP = [

        // USERS
        'uq_users_email' => [
            'field' => 'email',
            'message' => 'This email is already in use.'
        ],
        'uq_users_phone' => [
            'field' => 'phoneNumber',
            'message' => 'This phone number is already in use.'
        ],

        // CITIES
        'uq_cities_name' => [
            'field' => 'name',
            'message' => 'This city already exists.'
        ],

        // SUBURBS
        'fk_suburbs_city_id' => [
            'field' => 'city_id',
            'message' => 'City does not exist.'
        ],
        'uq_suburbs_city_name' => [
            'field' => 'name',
            'message' => 'This suburb already exists in this city.'
        ],

        // BUYER / SELLER INFO
        'uq_buyerSellerInfo_user_id' => [
            'field' => 'user_id',
            'message' => 'Buyer/Seller info already exists for this user.'
        ],
        'fk_buyerSellerInfo_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_buyerSellerInfo_suburb_id' => [
            'field' => 'suburb_id',
            'message' => 'Suburb does not exist.'
        ],
        'fk_buyerSellerInfo_img_id' => [
            'field' => 'img_id',
            'message' => 'Image does not exist.'
        ],

        // CATEGORIES
        'uq_categories_name' => [
            'field' => 'name',
            'message' => 'Category already exists.'
        ],

        // PRODUCTS
        'fk_products_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_products_category_id' => [
            'field' => 'category_id',
            'message' => 'Category does not exist.'
        ],

        // PRODUCT IMAGES
        'fk_productImgs_product_id' => [
            'field' => 'product_id',
            'message' => 'Product does not exist.'
        ],
        'fk_productImgs_img_id' => [
            'field' => 'img_id',
            'message' => 'Image does not exist.'
        ],

        // PRODUCT RESERVATIONS
        'fk_productReservations_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_productReservations_product_id' => [
            'field' => 'product_id',
            'message' => 'Product does not exist.'
        ],

        // ORDERS
        'fk_orders_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_orders_product_id' => [
            'field' => 'product_id',
            'message' => 'Product does not exist.'
        ],
        'fk_orders_seller_id' => [
            'field' => 'seller_id',
            'message' => 'Seller does not exist.'
        ],
        'fk_orders_reservation_id' => [
            'field' => 'reservation_id',
            'message' => 'Reservation does not exist.'
        ],

        // TRANSACTIONS
        'fk_transactions_reservation_id' => [
            'field' => 'reservation_id',
            'message' => 'Reservation does not exist.'
        ],
        'fk_transactions_order_id' => [
            'field' => 'order_id',
            'message' => 'Order does not exist.'
        ],
        'fk_transactions_affected_user_id' => [
            'field' => 'affected_user_id',
            'message' => 'User does not exist.'
        ],

        // REVIEWS
        'fk_reviews_product_id' => [
            'field' => 'product_id',
            'message' => 'Product does not exist.'
        ],
        'fk_reviews_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_reviews_img_id' => [
            'field' => 'img_id',
            'message' => 'Image does not exist.'
        ],
        'uq_reviews_user_product' => [
            'field' => 'user_id',
            'message' => 'You have already reviewed this product.'
        ],

        // SUBRATINGS
        'fk_subRatings_review_id' => [
            'field' => 'review_id',
            'message' => 'Review does not exist.'
        ],

        // QUESTIONARE
        'fk_questionareQuestions_questionare_id' => [
            'field' => 'questionare_id',
            'message' => 'Questionnaire does not exist.'
        ],
        'fk_questionareChoices_question_id' => [
            'field' => 'question_id',
            'message' => 'Question does not exist.'
        ],
        'fk_questionareResponses_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_questionareResponses_questionare_choice_id' => [
            'field' => 'questionare_choice_id',
            'message' => 'Choice does not exist.'
        ],
        'uq_questionareResponses_user_choice' => [
            'field' => 'user_id',
            'message' => 'You have already answered this question.'
        ],
        'fk_questionareUserAttempt_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_questionareUserAttempt_questionare_id' => [
            'field' => 'questionare_id',
            'message' => 'Questionnaire does not exist.'
        ],

        // VERIFICATION REQUEST
        'fk_verificationRequest_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'fk_verificationRequest_img_id_card_id' => [
            'field' => 'img_id_card_id',
            'message' => 'ID card image does not exist.'
        ],
        'fk_verificationRequest_img_selfie_id' => [
            'field' => 'img_selfie_id',
            'message' => 'Selfie image does not exist.'
        ],

        // AUTH LOG
        'fk_authLog_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],

        // USER LOGS
        'fk_userLogs_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],

        // BANK RECIPIENTS
        'fk_bankRecepients_user_id' => [
            'field' => 'user_id',
            'message' => 'User does not exist.'
        ],
        'uq_bankRecepients_recipient_code' => [
            'field' => 'recipient_code',
            'message' => 'Recipient code must be unique.'
        ],
    ];
}