// Admin side-la indha query use pannunga
<?php
$shop_id = $_GET['shop_id']; // URL-la irundhu shop id varum

$history_query = "SELECT * FROM orders 
                  WHERE shop_id = '$shop_id' 
                  ORDER BY order_date DESC";

$history_res = mysqli_query($conn, $history_query);
?>