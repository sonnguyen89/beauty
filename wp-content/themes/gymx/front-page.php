<?php 
	get_header();
	the_post();
	$top_padding = get_post_meta(get_the_ID(), 'gymx_post_top_padding', true);
	$bottom_padding = get_post_meta(get_the_ID(), 'gymx_post_bottom_padding', true);
?>
<div class="banner-wrapper">
    <div class="container">
        <div class="row" style="margin: 0">
            <div class="col-md-12">
                <!-- desktop banner -->
                <div class="background-banner">
                    <img src="/wp-content/uploads/2021/09/WechatIMG3285.jpeg"/>
                </div>
                <!-- mobile banner -->
                <div class="background-banner-for-mobile" >
                    <img src="/wp-content/uploads/2021/10/Screen-Shot-2021-10-03-at-11.03.50.png"/>
                </div>
            </div>
        </div>
    </div>
</div
<div class="wrapper <?php echo esc_attr($top_padding) . ' ' . esc_attr($bottom_padding); ?>" id="page-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 content-area">
                <a id="home" class="in-page-link" href="#"></a>
	            <?php the_content(); ?>    
            </div>
        </div>
    </div><!-- Container end -->
</div><!-- Wrapper end -->

<?php
	get_footer();