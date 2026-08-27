<?php
require_once 'connection.php';
$id=(int)($_POST['order_id']??0);$status=$_POST['status']??'';$allowed=['processing','packing','shipping','delivered','cancelled'];if($id>0&&in_array($status,$allowed,true)){ $st=mysqli_prepare($con,"UPDATE orders SET Status=? WHERE Order_id=?");mysqli_stmt_bind_param($st,'si',$status,$id);mysqli_stmt_execute($st);}redirect('dashboard.php#recent-orders');
