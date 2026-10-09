<?php include ('./conn/conn.php') ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDIAS SYSTEM</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
    <blade import|%20url(%26%2339%3Bhttps%3A%2F%2Ffonts.googleapis.com%2Fcss2%3Ffamily%3DPoppins%3Awght%40500%26display%3Dswap%26%2339%3B)%3B%0D>* {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
    }

    body {
        display: flex;
        flex-direction: column;
        align-items: center;
        /* background-image: url("https://images.unsplash.com/photo-1485470733090-0aae1788d5af?q=80&w=1517&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"); */
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-color: black;
        height: 200vh;
    }

    .content {
        backdrop-filter: blur(100px);
        color: rgb(255, 255, 255);
        padding: 40px;
        border: 2px solid;
        border-radius: 10px;
        margin-top: 100px;
    }

    .table {
        color: rgb(255, 255, 255) !important;
    }

    td button {
        font-size: 20px;
        width: 30px;
    }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary" style="width: 100%;">
        <a class="navbar-brand ml-5" href="home.php">Welcom</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarScroll"
            aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="./index.php">Log Out</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Update Modal -->
    <div class="modal fade mt-5" id="updateUserModal" tabindex="-1" aria-labelledby="updateUser" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateUserModal">Update User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="./endpoint/update-user.php" method="POST">
                        <div class="form-group row">
                            <div class="col-6">
                                <input type="text" name="tbl_user_id" id="updateUserID" hidden>
                                <label for="updateFirstName">Nom :</label>
                                <input type="text" class="form-control" id="updateFirstName" name="first_name">
                            </div>
                            <div class="col-6">
                                <label for="updateLastName">Prenom:</label>
                                <input type="text" class="form-control" id="updateLastName" name="last_name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-5">
                                <label for="updateContactNumber">Numero de telephone:</label>
                                <input type="number" class="form-control" id="updateContactNumber" name="contact_number"
                                    maxlength="11">
                            </div>
                            <div class="col-7">
                                <label for="updateEmail">Email:</label>
                                <input type="text" class="form-control" id="updateEmail" name="email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="updateUsername">Nom d'utilisateur :</label>
                            <input type="text" class="form-control" id="updateUsername" name="username">
                        </div>
                        <div class="form-group">
                            <label for="updatePassword">Mot de passe :</label>
                            <input type="text" class="form-control" id="updatePassword" name="password">
                        </div>
                        <button type="submit" class="btn btn-dark login-register form-control">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade mt-5" id="addmediaModal" tabindex="-1" aria-labelledby="addmediaModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateUserModal">Ajouter Media</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="./endpoint/add-media.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group row">
                            <div class="col-12">
                                <input type="text" name="tbl_user_id" id="updateUserID" hidden>
                                <label for="updateFirstName">Nom Media:</label>
                                <input type="text" class="form-control" id="updateFirstName" name="nomMedia">
                            </div>
                            <div class="col-12">
                                <label for="updateLastName">Detail Media:</label>
                                <input type="text" class="form-control" id="updateLastName" name="detailMedia">
                            </div>
                        </div>
                        <div class="form-group now">

                        </div>
                        <div class="form-group row">
                            <div class="col-12">
                                <label for="updateContactNumber">Description Media:</label>
                                <textarea type="number" class="form-control" id="updateContactNumber" name="descMedia"
                                    maxlength="80"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="updateEmail">Lien youtube:</label>
                                <input type="text" class="form-control" id="updateEmail" name="lien">
                            </div>

                            <div class="col-12">
                                <label for="updateEmail">Date publication:</label>
                                <input type="date" class="form-control" id="datepub" name="datepub">
                            </div>

                            <div class="col-12">
                                <label for="updateEmail">minMedia:</label>
                                <input type="text" class="form-control" id="updateEmail" name="minMedia">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="updateUsername">Categorie Media:</label>
                            <select class="form-control" name="categorieMedia">
                                <option>Movies</option>
                                <option>Tv Shows</option>
                                <option>Tips</option>
                                <option>Cartoon</option>
                                <option>Jeunesse</option>
                                <option>Boutiques</option>
                                <option>Shorts</option>
                                <option>Musics</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image">Image Media:</label>
                            <input type="file" class="form-control" id="image" name="imageMedia">
                        </div>
                        <div class="form-group">
                            <label for="updatePassword">Video Media:</label>
                            <input type="file" class="form-control" id="updatePassword" name="videoMedia">
                        </div>
                        <button type="submit" name="register"
                            class="btn btn-dark login-register form-control">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="content">
        <h4>List of medias</h4>
        <button id="addbtn" onclick="add_media()" type="button" class="btn btn-primary">AJOUTER MEDIA </button>
        <button type="button" class="btn btn-primary">AJOUTER USER </button>
        <hr>
        <table class="table table-hover table-collapse">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nom medias</th>
                    <th scope="col">Detail Media</th>
                    <th scope="col">Descrption Medias</th>
                    <th scope="col">Date publication</th>
                    <th scope="col">Categorie</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>

                <?php 
                
                    $stmt = $conn->prepare("SELECT * FROM `medias`");
                    $stmt->execute();

                    $result = $stmt->fetchAll();

                    foreach ($result as $row) {
                        $id_media = $row['id_media'];
                        $nom_media = $row['nomMedia'];
                        $detailMedia = $row['detailMedia'];
                        $descMedia = $row['descMedia'];
                        $datepub = $row['datePub'];
                        $categorieMedia = $row['categorieMedia'];
                        $lien = $row['lien'];
                        

                    ?>

                <tr>
                    <td id="id$id_media-<?= $id_media ?>"><?php echo $id_media ?></td>
                    <td id="firstName-<?= $id_media ?>"><?php echo $nom_media ?></td>
                    <td id="lastName-<?= $id_media ?>"><?php echo $detailMedia ?></td>
                    <td id="contactNumber-<?= $id_media ?>"><?php echo $descMedia ?></td>
                    <td id="email-<?= $id_media ?>"><?php echo $datepub ?></td>
                    <td id="username-<?= $id_media ?>"><?php echo $categorieMedia ?></td>

                    <td>
                        <button id="editBtn" onclick="update_user(<?php echo $id_media ?>)"
                            title="Edit">&#9998;</button>
                        <button id="deleteBtn" onclick="delete_user(<?php echo $id_media ?>)">&#128465;</button>
                    </td>
                </tr>

                <?php
                    }

                ?>
            </tbody>
        </table>
    </div>

    <script>
    // Update user
    function update_user(id) {
        $("#updateUserModal").modal("show");

        let updateUserID = $("#id$id_media-" + id).text();
        let updateFirstName = $("#firstName-" + id).text();
        let updateLastName = $("#lastName-" + id).text();
        let updateContactNumber = $("#contactNumber-" + id).text();
        let updateEmail = $("#email-" + id).text();
        let updateUsername = $("#username-" + id).text();
        let updatePassword = $("#password-" + id).text();

        console.log(updateFirstName);
        console.log(updateLastName);

        $("#updateUserID").val(updateUserID);
        $("#updateFirstName").val(updateFirstName);
        $("#updateLastName").val(updateLastName);
        $("#updateContactNumber").val(updateContactNumber);
        $("#updateEmail").val(updateEmail);
        $("#updateUsername").val(updateUsername);
        $("#updatePassword").val(updatePassword);

    }

    function add_media() {
        $("#addmediaModal").modal("show");



    }

    // Delete user
    function delete_user(id) {
        if (confirm("Do you want to delete this user?")) {
            window.location = "./endpoint/delete-user.php?user=" + id;
        }
    }
    </script>

    <!-- Bootstrap Js -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

</body>

</html>