<?php
	require_once __DIR__ .'/model.php';

class ProductSummary extends Model implements JsonSerializable{

    private $id;
    private $title;
    private $price;
    private $state;
    private $userSummary;
    private $suburb;
    private $productImg;
    
    // Build summary from query row
    protected static function fromArray(array $row,$prefix): ProductSummary  {
		$productSummary = new ProductSummary();

		$productSummary->id = $row[$prefix.'p_id'];
		$productSummary->title = $row[$prefix.'p_title'];
		$productSummary->price = $row[$prefix.'p_price'];
    	$productSummary->state = $row[$prefix.'p_state'];

    	$productSummary->setUserSummary(UserSummary::fromArray($row,"u_"));
    	$productSummary->setProductImg(ProductImage::fromArray($row,"pImg_"));

        $city = City::fromArray($row,"cit_");
        $suburb = Suburb::fromArray($row,"s_");
        $suburb->setCity($city);

        $productSummary->setSuburb($suburb);

        return $productSummary;
	}
    
    // JSON output
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'state' => $this->state,

            'userSummary' => $this->userSummary,
            'suburb' => $this->suburb,
            'productImg' => $this->productImg
        ];
    }
    
    // Get all products with filters
    public static function getAllProducts($filters,$orderBy,$dir){
        $params=[];
        $sql = self::baseQuery();

        $map = [
            'minPrice' => fn($v) => ['p.price >= ?', $v],
            'maxPrice' => fn($v) => ['p.price <= ?', $v],
            'banned' => fn($v) => ['u.banned = ?', $v],

            'category' => fn($v) => [
                "EXISTS (
                    SELECT 1 
                    FROM categories cat 
                    WHERE p.id = cat.id 
                    AND cat.id = ?
                )",
                $v
            ],

            'suburbId' => fn($v) => ['s.id = ?', $v],
            'cityId' => fn($v) => ['cit.id = ?', $v],
            'search' => fn($v) => ['p.title LIKE ?', "%$v%"],
            'userId' => fn($v) => ['u.id = ?', $v],
            'notUserId' => fn($v) => ['u.id <> ?', $v],
        ];
        
        $sql = self::applyFilters($sql,$params,$filters,$map);

        $orderMap = [
        	'id'    => 'p.id',
        	'price' => 'p.price',
    	];

		$sql = self::applySorting($sql,$orderBy,$orderMap,'p.id',$dir);
       
		$rows = Db::select($sql,$params);

        if(!$rows){
            return null;
        }

        return self::mapRows($rows,"");
    }
    
    // Base SQL query
    public static function baseQuery(){
        $sqlWith = "WITH u AS(". UserSummary::baseWithSuburb() .")";
   
        return $sqlWith."SELECT 
p.id AS p_id, p.title AS p_title, p.description AS p_description, p.price AS p_price, p.state AS p_state,
u.id AS u_id, u.username AS u_username,u.verified as u_verified,u.banned as u_banned,u.total_reviews as u_total_reviews,u.avg_rating as u_avg_rating,
cit.id AS cit_id, cit.name AS cit_name,
s.id AS s_id, s.name AS s_name,
pImgs.id as pImg_id,pImgs.img_id as pImg_img_id,pImgs.product_id as pImg_product_id,pImgs.is_primary as pImg_is_primary
FROM products AS p
INNER JOIN u ON u.id = p.user_id 
INNER JOIN suburbs AS s ON s.id = u.suburb_id
INNER JOIN cities AS cit ON cit.id = s.city_id
INNER JOIN productImgs AS pImgs ON pImgs.product_id=p.id
    AND pImgs.is_primary = 1
";     
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getPrice() {return $this->price;}
    
    public function getUserSummary(){return $this->userSummary;}
    public function setUserSummary($userSummary){$this->userSummary=$userSummary;}
    
    public function getSuburb(){return $this->suburb;}
    public function setSuburb($suburb){$this->suburb=$suburb;}
    
    public function getProductImg(){return $this->productImg;}
    public function setProductImg($productImg){$this->productImg=$productImg;}
}
?>