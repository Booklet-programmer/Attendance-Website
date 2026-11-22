<?php
require dirname(__DIR__) . '/app/bootstrap.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("Run this script from the command line only.\n"); }
echo "UPSA Smart QR - Initial Head Administrator setup\n";
$name=trim((string)readline("Full name: "));
$email=trim((string)readline("Email: "));
$username=trim((string)readline("Username: "));
$password=(string)readline("Password (input may be visible): ");
if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||$username===''||strlen($password)<12){fwrite(STDERR,"Provide valid details and a password of at least 12 characters.\n");exit(1);}
$hash=password_hash($password,PASSWORD_DEFAULT);
$stmt=db()->prepare("INSERT INTO administrators(full_name,staff_id,email,username,password_hash,role,account_status,department_id) VALUES(:name,:staff,:email,:username,:hash,'Head Administrator','Approved',NULL)");
$stmt->execute(['name'=>$name,'staff'=>'HEAD-'.strtoupper(bin2hex(random_bytes(3))),'email'=>$email,'username'=>$username,'hash'=>$hash]);
echo "Head Administrator created. Sign in at /login.php\n";
