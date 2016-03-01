<?php

namespace AWC\SocialPledge;

/**
 * SocialImages.php
 * Automatic resizing of images appropriate to specific social networks
 *
 * @package AWC\SocialPledge
 */
class SocialImages
{
    /**
     * Given an image URL and a size, generate the optimal size for the given social network and return a URL to that image.
     * If the image is already in the proper proportions then it will be used as is.
     */
    public static function resizeForNetwork($imageUrl, $imageId, $currentSize, $network) {
        $size = self::getPreferredSize($network, $currentSize);
        if($size == $currentSize)
            return $imageUrl;
        $imagePath = self::getImagePath($imageUrl);
        $target = self::getImagePathForSize($imagePath, $size);
        if(!file_exists($target) || filemtime($target) < filemtime($imagePath)) {
            if(!preg_match('/-\d+x\d+\.[^.]*$/', $imagePath)) {
                // meaning this is an original image and the watermark was likely not applied
                $post = get_post($imageId);
                $imagePath = self::addWatermark($imagePath, $post, $target);
            }
            self::addImagePadding($imagePath, $target, $currentSize, $size);
        }
        return self::getImageUrl($target);
    }

    /**
     * Return local path for the given image URL
     */
    public static function getImagePath($imageUrl) {
        $uploads = wp_upload_dir();
        // Check that the upload base exists in the file location.
        if ( 0 === strpos( $imageUrl, $uploads['baseurl'] ) ) {
            // Replace file location with url location.
            return str_replace($uploads['baseurl'], $uploads['basedir'], $imageUrl);
        }
        throw new \Exception("Unable to locate image path for $imageUrl");
    }

    private static function addWatermark($imagePath, $imagePost, $target) {
        if(!class_exists('EW_Plugin'))
            return $imagePath;
        $ewPlugin = new \EW_Plugin();
        $ewPlugin->plugin_init();
        $prop = new \ReflectionProperty('EW_Plugin', 'currentImage');
        if($prop) {
            $prop->setAccessible(true);
            $prop->setValue($ewPlugin, $imagePost);
        }
        $ew = $ewPlugin->getEasyWatermark();
        $imageType = 'image/jpeg';
        $ew->setImagePath($imagePath)
            ->setImageMime($imageType)
            ->setOutputFile($target)
            ->setOutputMime($imageType);

        if(!$ew->create() || !$ew->saveOutput()){
            return $imagePath;
            // $error = $ew->getError();
            // throw new \Exception("Error: $error");
        }
        $ew->clean();
        return $target;
    }

    private static function addImagePadding($imagePath, $targetPath, $originalSize, $targetSize) {
        $original = imagecreatefromstring(file_get_contents($imagePath));
        if(!$original)
            throw new \Exception("Unable to read image from $imagePath");

        $orig_w = $originalSize[0];
        $orig_h = $originalSize[1];
        $output_w = $targetSize[0];
        $output_h = $targetSize[1];

        // determine offset coords so that new image is centered
        $offest_x = ($output_w - $orig_w) / 2;
        $offest_y = ($output_h - $orig_h) / 2;

        // create new image and fill with background colour
        $newimg = imagecreatetruecolor($output_w, $output_h);
        $bgcolor = imagecolorallocate($newimg, 255, 255, 255); 
        imagefill($newimg, 0, 0, $bgcolor); // fill background colour

        // copy and resize original image into center of new image
        if(!imagecopy($newimg, $original, $offest_x, $offest_y, 0, 0, $orig_w, $orig_h))
            throw new \Exception('imagecopy failed');

        //save it
        if(!imagejpeg($newimg, $targetPath, 80))
            throw new \Exception('imagejpeg failed');
    }

    /**
     * Return URL given an image path
     */
    private static function getImageUrl($imagePath) {
        $uploads = wp_upload_dir();
        // Check that the upload base exists in the file location.
        if ( 0 === strpos( $imagePath, $uploads['basedir'] ) ) {
            // Replace file location with url location.
            return str_replace($uploads['basedir'], $uploads['baseurl'], $imagePath);
        }
        throw new \Exception("Unable to locate image URL for $imagePath");
    }

    /**
     * Given a path, add the size parameter to it
     */
    private static function getImagePathForSize($imagePath, $size) {
        return preg_replace('/(-\d+x\d+)?\.([^\.]+)$/', "-$size[0]x$size[1].$2", $imagePath);
    }

    private static function getPreferredSize($network, $currentSize) {
        $r = $currentSize[0] / $currentSize[1];
        switch($network) {
        case 'facebook':
            $rtarget = 1.91;
            break;
            // for twitter we can leave it vertical or horizontal
        case 'twitter':
            $rtarget = 1;
            break;
        default:
            $rtarget = 4 / 3;
        }
        if($r < $rtarget) {
            // too narrow!  need to increase width
            return [
                round($currentSize[1] * $rtarget), $currentSize[1]
            ];
        } else {
            // too wide!  need to increase height
            return [
                $currentSize[0], round($currentSize[0] / $rtarget)
            ];
        }
        return [1024, 768];
    }
}
