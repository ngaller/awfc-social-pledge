<!doctype html>
<html>
<head>
    <?php
    /**
     * social_share.php
     * Custom template used for the "Social Share" custom post type.
     * The template renders meta tags appropriate for the social network the share was generated for and updates the crawl
     * flag.
     * If the user agent is not a crawler, then the template instead performs a redirection to the exhibition's URL.
     */

    while (have_posts()) {
        the_post();
        $shareData = \AWC\SocialPledge\SocialSharePostType::getSocialMetaData();
        $shareData->generateMetaTags();
    }
    ?>
</head>
<body>
</body>
</html>
