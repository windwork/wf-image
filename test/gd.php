<?php 
PHP_SAPI == 'cli' || die('access denied');
$start = microtime(1);

require_once '../lib/IImage.php';
require_once '../lib/Exception.php';
require_once '../lib/strategy/GD.php';

/**
 * 
 */
function testThumb(\wf\image\IImage $img, $width, $height, $dist, $isCut = true, $cutPos = 5) 
{
	$thumbCtx = $img->thumb($width, $height, $isCut, $cutPos);
	if($thumbCtx) {
		file_put_contents($dist, $thumbCtx);
	}
}

/**
 * 
 */
function testWatermark(\wf\image\IImage $img, $dist, $watermarkPlace = 9, $quality = 95, $waterFile = 'src_image/logo.png') 
{
	$ret = $img->watermark($waterFile, $watermarkPlace, $quality);
	file_put_contents($dist, $ret);
}

$img = new \wf\image\strategy\GD();

# 缩略图
// test png
$img->setImage(file_get_contents('src_image/1.png'));

testThumb($img, 0,   200, 'dist_image/thumb.png.cut_0x200.jpg', true);
testThumb($img, 0,   400, 'dist_image/thumb.png.cut_0x400.jpg', true);
testThumb($img, 300, 0,   'dist_image/thumb.png.cut_300x0.jpg', true);
testThumb($img, 600, 0,   'dist_image/thumb.png.cut_600x0.jpg', true);
testThumb($img, 500, 300, 'dist_image/thumb.png.cut_500x300.jpg', true);

testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-1.jpg', true, 1);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-2.jpg', true, 2);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-3.jpg', true, 3);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-4.jpg', true, 4);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-5.jpg', true, 5);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-6.jpg', true, 6);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-7.jpg', true, 7);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-8.jpg', true, 8);
testThumb($img, 500, 500, 'dist_image/thumb.png.cut_500x500-9.jpg', true, 9);

testThumb($img, 300, 600, 'dist_image/thumb.png.cut_300x600.jpg', true);

testThumb($img, 300, 600, 'dist_image/thumb.png.uncut_300x600.jpg', false);
testThumb($img, 600, 300, 'dist_image/thumb.png.uncut_600x300.jpg', false);

// test jpg
$img->setImage(file_get_contents('src_image/2.jpg'));

testThumb($img, 0,   200, 'dist_image/thumb.jpg.cut_0x200.jpg', true);
testThumb($img, 300, 0,   'dist_image/thumb.jpg.cut_300x0.jpg', true);
testThumb($img, 400, 200, 'dist_image/thumb.jpg.cut_400x200.jpg', true);
testThumb($img, 200, 400, 'dist_image/thumb.jpg.cut_200x400.jpg', true);
testThumb($img, 400, 200, 'dist_image/thumb.jpg.uncut_400x200.jpg', false);
testThumb($img, 200, 400, 'dist_image/thumb.jpg.uncut_200x400.jpg', false);

testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-1.jpg', true, 1);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-2.jpg', true, 2);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-3.jpg', true, 3);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-4.jpg', true, 4);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-5.jpg', true, 5);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-6.jpg', true, 6);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-7.jpg', true, 7);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-8.jpg', true, 8);
testThumb($img, 500, 500, 'dist_image/thumb.jpg.cut_500x500-9.jpg', true, 9);

// test gif
$img->setImage(file_get_contents('src_image/3.gif'));
testThumb($img, 0,   100, 'dist_image/thumb.gif.cut_0x100.jpg', true);
testThumb($img, 200, 0,   'dist_image/thumb.gif.cut_200x0.jpg', true);
testThumb($img, 200, 100, 'dist_image/thumb.gif.cut_200x100.jpg', true);
testThumb($img, 100, 200, 'dist_image/thumb.gif.cut_100x200.jpg', true);
testThumb($img, 200, 100, 'dist_image/thumb.gif.uncut_200x100.jpg', false);
testThumb($img, 100, 200, 'dist_image/thumb.gif.uncut_100x200.jpg', false);

# 打水印
$img->setImage(file_get_contents('src_image/1.png'));
testWatermark($img, 'dist_image/water.png.jpg');

$img->setImage(file_get_contents('src_image/2.jpg'));
testWatermark($img, 'dist_image/water.jpg.jpg');

$img->setImage(file_get_contents('src_image/3.gif'));
testWatermark($img, 'dist_image/water.gif.jpg');

$end = microtime(1);
$time = $end - $start;

print "测试完成，\n用时{$time}秒";
