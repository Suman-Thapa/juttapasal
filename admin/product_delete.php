<?php
require_once 'connection.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){redirect('products.php');}$id=(int)($_POST['id']??0);if($id>0){$st=mysqli_prepare($con,"DELETE FROM product WHERE Product_id=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);}redirect('products.php');
