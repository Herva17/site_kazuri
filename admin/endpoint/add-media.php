<?php
include('../conn/conn.php');


if (isset($_POST['register'])) {
    try {
        $nomMedia = $_POST['nomMedia'];
        $detailMedia = $_POST['detailMedia'];
        $descMedia = $_POST['descMedia'];
        $lien = $_POST['lien'];
        $datepub = $_POST['datepub'];
        $minMedia = $_POST['minMedia'];
        $categorieMedia = $_POST['categorieMedia'];
     

        $imageMedia = $_FILES['imageMedia']['name'];
        $destination='medias/'.$imageMedia;
        $imagepath = pathinfo($destination, PATHINFO_EXTENSION);
        $valid_extension = array("jpg","png","gif");
        if(!in_array(strtolower($imagepath), $valid_extension)){

        }

        if(!move_uploaded_file($_FILES['imageMedia']['tmp_name'], $destination)){

        }

        
        $videoMedia = $_FILES['videoMedia']['name'];
        $locations='medias/'.$videoMedia;
        $imagepath = pathinfo($videoMedia, PATHINFO_EXTENSION);
        $valid_extension = array("avi', 'flv', 'wmv', 'mov', 'mp4");
        if(!in_array(strtolower($imagepath), $valid_extension)){

        }

        if(!move_uploaded_file($_FILES['videoMedia']['tmp_name'], $locations)){

        }

      

        $conn->beginTransaction();
    
        $stmt = $conn->prepare("SELECT `nomMedia`, `detailMedia` FROM `medias` WHERE `nomMedia` = :nomMedia AND `detailMedia` = :detailMedia");
        $stmt->execute([
            'nomMedia' => $nomMedia,
            'detailMedia' => $detailMedia
        ]);
        $nameExist = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (empty($nameExist)) {
            
    
            $insertStmt = $conn->prepare("INSERT INTO `medias` (`nomMedia`, `detailMedia`, `descMedia`, `lien`, `datePub`, `minMedia`, `categorieMedia`,`imageMedia`,`videoMedia`) VALUES (:nomMedia, :detailMedia, :descMedia, :lien, :datePub, :minMedia, :categorieMedia,  :imageMedia, :videoMedia)");
            $insertStmt->bindParam(':nomMedia', $nomMedia, PDO::PARAM_STR);
            $insertStmt->bindParam(':detailMedia', $detailMedia, PDO::PARAM_STR);
            $insertStmt->bindParam(':descMedia', $descMedia, PDO::PARAM_STR);
            $insertStmt->bindParam(':lien', $lien, PDO::PARAM_STR);
            $insertStmt->bindParam(':datePub', $datepub, PDO::PARAM_STR);
            $insertStmt->bindParam(':minMedia', $minMedia, PDO::PARAM_STR);
            $insertStmt->bindParam(':categorieMedia', $categorieMedia, PDO::PARAM_STR);
            $insertStmt->bindParam(':imageMedia', $imageMedia, PDO::PARAM_STR);
            $insertStmt->bindParam(':videoMedia', $videoMedia, PDO::PARAM_STR);
            $insertStmt->execute();
   

            $conn->commit();
        } else {
            echo "
            <script>
                alert('User Already Exists');
                window.location.href = 'http://localhost/Projects/KazuriApp/admin/home.php';
            </script>
            ";
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_POST['verify'])) {

    try {
        $userVerificationID = $_POST['user_verification_id'];
        $verificationCode = $_POST['verification_code'];
    
        $stmt = $conn->prepare("SELECT `verification_code` FROM `tbl_user` WHERE `tbl_user_id` = :user_verification_id");
        $stmt->execute([
            'user_verification_id' => $userVerificationID,
        ]);
        $codeExist = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($codeExist && $codeExist['verification_code'] == $verificationCode) {
            session_destroy();
            echo "
            <script>
                alert('Registered Successfully.');
                window.location.href = 'http://localhost/login-system-with-lien-verification/index.php';
            </script>
            ";
        } else {
            $conn->prepare("DELETE FROM `tbl_user` WHERE `tbl_user_id` = :user_verification_id")->execute([
                'user_verification_id' => $userVerificationID
            ]);

            echo "
            <script>
                alert('Incorrect Verification Code. Register Again.');
                window.location.href = 'http://localhost/login-system-with-lien-verification/index.php';
            </script>
            ";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>