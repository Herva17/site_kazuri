<?php

$p = app\App::getInstance()->getTable('media')->PlayMedia($_GET['id_media']);
//var_dump($p);die();


?>

<header class="page-header single" data-background="images/movie-poster-bg.jpg">
    <div class="container">
        <div class="row">
            <?php foreach($p as $v): ?>

            <div class="col-12">

                <div class="video-player">
                    <a href="index.php?p=welcome" class="back-btn"><i class="lni lni-chevron-left"></i> RETOUR</a>
                    <video src="../admin/endpoint/medias/<?=$v[6] ?>" poster="../admin/endpoint/medias/" controls
                        playsinline></video>
                </div>

                <!-- end video-player -->
            </div>
            <?php endforeach ?>

            <!-- end col-12 -->
        </div>
        <!-- end col-12 -->
        <!-- end row -->
    </div>
    <!-- end container -->
</header>
<!-- end header -->
<section class="content-section" data-background="#ffffff">
    <div class="container">
        <div class="row">
            <?php foreach($p as $v): ?>
            <div class="col-lg-8">
                <div class="movie-info-box">
                    <h2 class="name">
                        <?=$v[1]?> <br />
                        <strong> <?=$v[2]?></strong>
                    </h2>
                    <ul class="features">
                        <li>
                            <div class="rate">
                                <svg class="circle-chart" viewBox="0 0 30 30" width="40" height="40" fill="transparent"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle class="circle-chart__background" stroke="#eee" stroke-width="2" fill="none"
                                        cx="15" cy="15" r="14"></circle>
                                    <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                        stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                                </svg>
                                <b>5.4</b> IMDB SCORE
                            </div>
                            <!-- end rate -->
                        </li>
                        <li>
                            <div class="year"><?=$v[4]?></div>
                        </li>
                        <li>
                            <div class="hd">1080 <b>HD</b></div>
                        </li>
                        <li>
                            <div class="tags"><?=$v[8]?></div>
                        </li>
                    </ul>
                    <p class="description">
                        <?=$v[3]?>
                    </p>
                    <a href="#" class="add-btn">+ ADD YOUR LIST</a>

                    <div class="rate-box">
                        <a href="#"><i class="lni lni-thumbs-up"></i></a>
                        <a href="#"><i class="lni lni-thumbs-down"></i></a>
                        <strong>61% liked this film</strong>
                    </div>
                </div>
                <!-- end movie-info-box -->
            </div>
            <?php endforeach ?>
            <!-- end col-8 -->
            <div class="col-lg-4">
                <div class="movie-side-info-box">
                    <figure><img src="../assets/ico/Logo2_Black.png" alt="KAZURI" /></figure>
                    <!-- <ul>
                        <li><strong>Initial release: </strong> Sept 16, 2022</li>
                        <li><strong>Director:</strong> Carles Torras</li>
                        <li><strong>Production:</strong> digiflex</li>
                        <li><strong>Screenplay:</strong> Carles Torras</li>
                    </ul> -->
                </div>
                <!-- end movie-side-info-box -->
            </div>
            <!-- end col-4 -->
        </div>
        <!-- end col-12 -->
        <!-- end row -->
    </div>
    <!-- end container -->
</section>
<!-- end content-section -->
<section class="content-section" data-background="#111111">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title light">
                    <h6>FIND ANYWHERE ELSE</h6>
                    <h2>Related Movies</h2>
                </div>
                <!-- end section-title -->
            </div>
            <!-- end col-12 -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image">
                        <a href="#"><img src="images/movies07.jpg" alt="Image" /></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.7</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content">
                        <small class="range">190 min,</small>
                        <ul class="tags">
                            <li>Romance</li>
                        </ul>
                        <div class="age">PG13</div>
                        <!-- end age -->
                        <h3 class="name"><a href="#">Secret Michael's Staff</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image">
                        <a href="#"><img src="images/movies08.jpg" alt="Image" /></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.7</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content">
                        <small class="range">190 min,</small>
                        <ul class="tags">
                            <li>Romance</li>
                        </ul>
                        <div class="age">PG13</div>
                        <!-- end age -->
                        <h3 class="name"><a href="#">Trolls: World Tour</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image">
                        <a href="#"><img src="images/movies09.jpg" alt="Image" /></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.7</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content">
                        <small class="range">190 min,</small>
                        <ul class="tags">
                            <li>Romance</li>
                        </ul>
                        <div class="age">PG13</div>
                        <!-- end age -->
                        <h3 class="name"><a href="#">Thrill Crazy</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image">
                        <a href="#"><img src="images/movies10.jpg" alt="Image" /></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.7</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content">
                        <small class="range">190 min,</small>
                        <ul class="tags">
                            <li>Romance</li>
                        </ul>
                        <div class="age">PG13</div>
                        <!-- end age -->
                        <h3 class="name"><a href="#">Not Out</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image">
                        <a href="#"><img src="images/movies11.jpg" alt="Image" /></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.7</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content">
                        <small class="range">190 min,</small>
                        <ul class="tags">
                            <li>Romance</li>
                        </ul>
                        <div class="age">PG13</div>
                        <!-- end age -->
                        <h3 class="name"><a href="#">World of War</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image">
                        <a href="#"><img src="images/movies12.jpg" alt="Image" /></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.7</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content">
                        <small class="range">190 min,</small>
                        <ul class="tags">
                            <li>Romance</li>
                        </ul>
                        <div class="age">PG13</div>
                        <!-- end age -->
                        <h3 class="name"><a href="#">Love Everywhere</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->
            <div class="col-12 text-center">
                <a href="#" class="custom-button">BROWSE ALL MOVIES</a>
            </div>
            <!-- end col-12 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>
<!-- end content-section -->
<section class="content-section" data-background="#ffffff">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h6>SHARE WITH US</h6>
                    <h2>All Comments</h2>
                </div>
                <!-- end section-title -->
            </div>
            <!-- end col-12 -->
            <div class="col-12">
                <div class="comments">
                    <ul class="comments-list">
                        <li>
                            <figure class="avatar">
                                <img src="images/avatar01.jpg" alt="Image" />
                            </figure>
                            <div class="comment">
                                <h6>Jenny Klausein</h6>
                                <p>
                                    Users like you see an increase in open rates when
                                    including recipients’ first and last name in the “To”
                                    field to personalize their emails. Gravity letters it
                                    amongst herself dearest an windows by. Wooded ladies she
                                    basket season age her uneasy saw.
                                </p>
                                <small>November 25, 2022</small>
                                <a href="#">REPLY</a>
                            </div>
                            <!-- end comment -->
                        </li>

                    </ul>
                    <div class="comments-form">
                        <h5>Leave a Reply</h5>
                        <div class="form-group">
                            <textarea placeholder="Comment"></textarea>
                        </div>
                        <!-- end form-group -->
                        <div class="form-group">
                            <input type="submit" value="Post Comment" />
                        </div>
                        <!-- end form-group -->
                    </div>
                    <!-- end comments-form -->
                </div>
                <!-- end comments -->
            </div>
            <!-- end col-12 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>