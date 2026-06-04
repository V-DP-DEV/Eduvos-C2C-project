<?php 
	require_once __DIR__ ."/../bootstrap.php";
	
	if($_SERVER['REQUEST_METHOD'] !== "POST"){
        echo getJsonWrongMethod('POST');
        return;
    }
	$id=[];
	
	try{
        $id=$_POST['id']??"";
        
        $userId=$_POST['userId']??"";
        
        Db::connect();
        $transactions=[];
        if($id){
            $transactions= Transaction::where(" id=? AND status='pending'",[$id]);
        }
        elseif($userId){
            $transactions = Transaction::where(" affected_user_id=? and status='pending'",[$userId]);
        }
        else{
            echo getJsonFailure("No params passed");
            return;
        }
        if(!$transactions){
            echo getJsonSuccess();
            return;
        }
        
        foreach($transactions as $transaction){
			
            $url="";
            if($transaction->getRefundId()){
                $url="https://api.paystack.co/refund/".urlencode($transaction->getRefundId());
            }
            else{
                $url = "https://api.paystack.co/transaction/verify/" . urlencode($transaction->getReference());
            }

            $result=null;
            //mocks transaction payout getting payed successfully
            if($transaction->getType()==="payout"){
                $result=Paystack::mockPayout($transaction->getReference());
            }
            elseif($transaction->getRefundId()){
                $result = Paystack::getRefund($transaction->getRefundId());
            }
            else{
                $result = Paystack::getTransaction($transaction->getReference());
            }
            
            if($result['status'] == true){
                $data = $result['data'];
                if($data['status']==="success" || $data['status']==="processed"){
                    if($transaction->getType()==="payment"){
                        $reservation = Reservation::getReservationIfActive($transaction->getReservationId());
                        if($reservation){
                            //reservation in time
                            $order = new Order();
        					$order->setUserId($reservation->getUserId());
        					$order->setProductId($reservation->getProductId());
        					$order->setPrice($transaction->getAmount());
        					$order->setReservationId($reservation->getId());
        					$order->save();
        					
                            Reservation::updateState($reservation->getId(),'completed');
        					Product::updateProductState($reservation->getProductId(),'unavailable');
                        }
                        else{
                            $response = Paystack::refundTransaction($transaction->getReference());
        					var_dump($transaction);
        					var_dump($response);
        					if ($response['status']) {
    							$refundId = $response['data']['id'];           // Paystack refund ID
    							$transactionId = $response['data']['transaction']['id']; // Paystack transaction ID

   								$transactionRefund=new Transaction();
        						$transactionRefund->setReservationId($transaction->getReservationId());
        						$transactionRefund->setAmount($transaction->getAmount());
       							$transactionRefund->setType('timeout_refund');
       							$transactionRefund->setReference("TXN_" . time() . "_" . rand(1000,9999));
        						$transactionRefund->setRelatedId($transactionId);
        						$transactionRefund->setRefundId($refundId);
                                $transactionRefund->setAffectedUser($transaction->getAffectedUserId());
       							$transactionRefund->save();
                        	}
                    	}
                    }
                    if($transaction->getType()==="refund"){
                        $order = Order::find($transaction->getOrderId());
                        $order->updateState('reversed');
                        Product::updateProductState($order->getProductId(),'available');
                    }
                    if($transaction->getType()==="payout"){
                        //update order to completed
                        $order= new Order();
                        $order->setId($transaction->getOrderId());
                        $order->updateState("completed");
                    }
                }
                if($data['status']==="failed"){
                    if($transaction->getType()==="payment"){
                        //update reservation to cancelled
                    }
                }
                
                if($data['status']!=="pending" && $data['status']!=="processing" ){
                    $status=$data["status"];
                    if($status==="processed"){
                        $status="success";
                    }
                    $transaction->updateState($status);
                }
            }
        }
        
        echo getJsonSuccess();
        return;
    }
	catch(PDOException $e){
		Db::handleException($e);
    }
    catch(Exception $e){
		echo getJsonGeneralFailure();
    }
?>