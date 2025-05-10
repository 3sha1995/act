<?php 
require_once '../CMS-WMSU-Website/classes/pages.class.php';
require_once '../CMS-WMSU-Website/tools/functions.php';

$homepageObj = new Pages;

$homeNewsSQL = "SELECT * from page_sections WHERE pageID = 1 AND indicator = 'Wmsu News';
";
$researchArchSQL = "SELECT * from page_sections WHERE pageID = 1 AND indicator = 'Research Archives';
";

$aboutUsSQL = "SELECT * from page_sections LEFT JOIN subpages ON content = subPageName WHERE pageID = 1 AND indicator = 'About WMSU';
";

$presCornerSQL = "SELECT * from page_sections WHERE pageID = 1 AND indicator = 'Pres Corner'; 
";

$servicesSQL = "SELECT * from page_sections WHERE pageID = 1 AND indicator = 'Services';
";

$homeNewsItems = $homepageObj->execQuery($homeNewsSQL);
$researchArchItems = $homepageObj->execQuery($researchArchSQL);
$aboutUsItems = $homepageObj->execQuery($aboutUsSQL);
$presCornerItems = $homepageObj->execQuery($presCornerSQL);
$servicesItems = $homepageObj->execQuery($servicesSQL);
foreach ($homeNewsItems as $items){
    if ($items['description'] == 'news-title'){
        $newsTitles[] = $items['content'];
    }
    if ($items['description'] == 'news-img'){
        $newsImgs[] = [
            'imagePath' => $items['imagePath'],
            'alt' => $items['alt']
        ];
    }
    if ($items['description'] == 'news-content'){
        $newsContent[] = $items['content'];
    }
}

foreach ($researchArchItems as $items){
    if ($items['description'] == 'research-title'){
        $researchTitles[] = $items['content'];
    }
    if ($items['description'] == 'research-author'){
        $researchAuthors[] = $items['content'];
    }
    if ($items['description'] == 'research-description'){
        $researchDesc[] = $items['content'];
    }
    if ($items['description'] == 'research-pub-date'){
        $researchPubDate[] = $items['content'];
    }
}

foreach ($aboutUsItems as $items){
    if ($items['description'] == 'about-description'){
        $aboutUsDesc = $items['content'];
    }
    if ($items['description'] == 'about-links'){
        $aboutUsLinks[] = [
            'content' => $items['content'],
            'link' => $items['subPagePath']
        ];
    }
}

foreach ($presCornerItems as $items){
    if ($items['description'] == 'report-title'){
        $reportTitles[] = $items['content'];
    }
    if ($items['description'] == 'report-date'){
        $reportDates[] = $items['content'];
    }
}

foreach($servicesItems as $items){
    if ($items['description'] == 'service-title'){
        $serviceTitles[] = $items['content'];
    }
    if ($items['description'] == 'service-imgs'){
        $serviceImages[] = $items['imagePath'];
    }
}
?>

<section class="hero-section-cont">
    <div class="homepage-video-container">
        <video id="delayedVideo" class="homepage-background-video" muted loop>
            <source src="../imgs/WMSU profile 2024.mp4" type="video/mp4">
        </video>
        <div class="Hero-Title-Cont">
            <div class="hero-divider divider-upper"></div>
            <p class="Hero-Title inter-bold">WESTERN MINDANAO</p>
            <p class="Hero-Title Lower-Title inter-bold">STATE UNIVERSITY</p>
            <p class="Hero-Subtitle inter-light">Your Future Starts Here: Learn, Innovate, Lead at WMSU!</p>
            <div class="hero-divider divider-lower"></div>
            <a class="hero-apply" href=""><div class="hero-button inter-light">APPLY</div></a>
            <img class="hero-arrowdown" src="../imgs/down-arrow.png" alt="">
        </div>
    </div>
</section>

<section class="line-after-hero"></section>

<section class="wmsu-news">
    <div class="news-header-container">
        <h2 class="news-title">WMSU NEWS</h2>
        <div class="more">
            <h6 class="inter-extrabold" id="more-article">MORE ARTICLES</h6>
        </div>
    </div>
    <div class="news-grid">
        <?php 
        
         for($i = 0; $i < count($newsTitles); $i++){
        ?>
            <div class="news-item">
            <div class="news-item-content">
                <img src="<?php echo $newsImgs[$i]['imagePath']?>" alt="<?php echo $newsImgs[$i]['alt']?>">
                <h6 class = "inter-medium"><?php echo $newsTitles[$i]?></h6>
                <p class = "inter-light"><?php echo $newsContent[$i]?></p>
                <a href="#" class="read-more">Read More ></a>
            </div>
        </div>
        <?php } ?>
    </div>
        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
        <span class="carousel-dot active"></span>
        <span class="carousel-dot"></span>
        <span class="carousel-dot"></span>
        <span class="carousel-dot"></span>
    </div>
</section>


<section class="line-page-div"></section>

<section class="research-archives">
    <a href="#" class="learn-more-top">
        <span class="learn-more-text inter-extrabold">LEARN MORE</span>
        <span class="learn-more-plus inter-extrabold">+</span>
    </a>
    <div class="research-header">
        <h2 class="research-title">RESEARCH ARCHIVES</h2>
    </div>
    
    <div class="research-content">

    <?php for ($i = 0; $i < count($researchTitles); $i++){
            
            ?>
        <div class="research-text">

            <h3 class="article-title"><?php echo $researchTitles[$i]?></h3>
            
            <p class="researcher"><?php echo $researchAuthors[$i]?></p>
            
            <p class="description"><?php echo $researchDesc[$i]?></p>
            
            <div class="article-meta">
                <span class="publish-date"><?php echo $researchPubDate[$i]?></span>
                <a href="#" class="read-more">Read More ></a>
            </div>
        </div>

    <?php } ?>
        
        <div class="research-image">
            <img src="../imgs/research.png" alt="Research Image">
        </div>
    </div>
</section>

<section class="line-page-div"></section>

<div class="about-page-title">
    <h1>ABOUT WMSU</h1>
    <a href="#" class="about-learn-more">
        <span class="about-learn-more-text inter-extrabold">Learn more</span>
        <span class="about-learn-more-plus inter-semibold">+</span>
    </a>
</div>
       <section class="about-section">
    <div class="about-container">
        <div class="vertical-divider"></div>
        <div class="about-content">
            <p class="about-description inter-extralight">
                <?php echo $aboutUsDesc?>.
            </p>
            <div class="about-links">

            <?php foreach ($aboutUsLinks as $items){?>
                <a href="<?php echo $items['link']?>" class="about-link inter-semibold">
                    <span><?php echo $items['content']?></span>
                    <span class="arrow">></span>
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<section class="line-page-div"></section>

<section class="presidents-corner">
    <div class="corner-container">
        <div class="corner-image">
            <img src="../imgs/OCHO.png" alt="WMSU President" class="img-fluid">
        </div>
        <div class="corner-content">
            <h2 class="section-title">PRESIDENT'S CORNER</h2>
            
            <div class="report-links">
            
            <?php for ($i = 0; $i < count($reportTitles); $i++) {?>
                <a href="#" class="report-item">
                    <div class="report-info">
                        <h3 class ="inter-bold"><?php echo $reportTitles[$i]?></h3>
                        <span class="report-date"><?php echo $reportDates[$i]?></span>
                    </div>
                </a>

            <?php } ?>
            </div>
        </div>
    </div>
</section>

<section class="line-page-div"></section>
</section>

<section class="wmsu-campuses">
    <div class="main-page-titles">WMSU CAMPUSES</div>
    <div class="camp-cont"> 
        <div class="camp-cont-left"> 
            <div class="camp-text"> 
                <div class="camp-text-title">ZAMBOANGA DEL SUR</div>
                <div class="camp-text-mix">
                    <div class="camp-text-plus">+</div>
                    <div class="camp-text-show">SHOW MORE</div>
                </div>
            </div>
        </div>
        <div class="camp-cont-mid"></div>
        <div class="camp-cont-right"> 
            <div class="camp-text"> 
                <div class="camp-text-title">ZAMBOANGA SIBUGAY</div>
                <div class="camp-text-mix">
                <div class="camp-text-plus">+</div>
                <div class="camp-text-show">SHOW MORE</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="line-page-div"></section>

<section class="wmsu-services">
    <div class="main-page-titles">SERVICES</div>
    <div class="services-cont">

    <?php for($i = 0; $i < count($serviceTitles); $i++) {?>
        <div class="services-squares">
            <div class="square-cont">
                <div class="square-raindrop">
                    <div class="square-outer"></div>
                    <div class="square-inner"></div>
                    <div class="square-icon">
                        <img src="<?php echo $serviceImages[$i]?>" alt="">
                    </div>
                </div>
                <div class="square-text"><?php echo $serviceTitles[$i]?></div>
            </div>
        </div>
       
        <?php } ?>

        <div class="services-rectangle">
            <div class="rectangle-text">
                <div class="rectangle-text-hide">WMSU<br>SERVICES</div>
                <div class="rectangle-plus">+</div>
                <div class="rectangle-more">MORE</div> 
            </div>
        </div> 
    </div>
</section>

<section class="line-page-div"></section>

<section class="follow-wmsu">
    <div class="main-page-titles">FOLLOW WMSU</div>
    <div class="follow-cont">
        <div class="follow-cont-left">
            <div class="follow-left-rect">
                <div class="left-red-overlay"></div>
                <img src="../imgs/facebook.jpg" alt="" class="follow-left-pic">
                <div class="follow-left-text">
                    <div class="left-text-icon">
                        <img src="../imgs/facebook-icon.png" alt="" >
                    </div>
                    <div class="left-text-word">FACEBOOK</div>
                    </div>
                </div>
            </div>
        <div class="follow-cont-right">
            <div class="follow-right-rect">
                <div class="right-red-overlay"></div>
                <img src="../imgs/youtube.jpg" alt="" class="follow-right-pic">
                <div class="follow-right-text">
                    <div class="right-text-word">YOUTUBE</div>
                    <div class="right-text-icon">
                        <img src="../imgs/youtube-icon.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


