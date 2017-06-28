Windwork 图片处理组件
=============================
支持图片缩略图和打水印，兼容各种第三方云存贮。
处理图片后，为方便云存贮处理，不直接保存图片，而是返回图片二进制内容。

## 生成缩略图
```
$img = wfImage();
// 设置源图片内容
$img->setImage(file_get_contents('src_image/1.png'));

// 生成200x200的缩略图，超过比例截掉
$cutPos = 5; // 裁剪后保留位置 1:左上, 2：中上， 3右上, 4：左中， 5：中中， 6：右中，7：左下， 8：中下，9右下
$dist = 'dist_image/thumb.png.cut_100x200.jpg'; // 图片保存路径
$imgCtx = $img->thumb(200, 200, true, $cutPos); // 为方便云存贮处理，不直接保存图片，而是返回图片二进制内容

file_put_contents($dist, $imgCtx);

// 生成100x200的缩略图，超过比例则补白色背景
$img->thumb(100, 200, false);

```

## 打水印

```
$img = wfImage();

// 设置源图片内容
$img->setImage(file_get_contents('src_image/1.png'));

// 给图片内容打水印后，保存到'dist_image/water.png.jpg'
$ret = $img->watermark('src_image/logo.png');
file_put_contents('dist_image/water.png.jpg', $ret


```

## Windwork图片处理接口
```
<?php

namespace wf\image;

/**
 * 图像处理接口
 */
interface ImageInterface {
    
    /**
     * 设置图片二进制内容
     * 
     * @param string $imageContent
     * @return \wf\image\Image
     */
    public function setImage($imageContent);
    
    /**
     * 设置图片背景颜色，当图片透明时或不截取图片
     * 
     * @param int $r 0 - 255
     * @param int $g 0 - 255
     * @param int $b 0 - 255
     */
    public function setBgColor($r, $g, $b);
    
    /**
     * 生成缩略图
     * 宽度不小于$thumbWidth或高度不小于$thumbHeight的图片生成缩略图
     * 建议缩略图和被提取缩略图的文件放于同一目录，文件名为“被提取缩略图文件.thumb.jpg”
     *
     * @param int $thumbWidth
     * @param int $thumbHeight
     * @param bool $isCut = true 是否裁剪图片，true）裁掉超过比例的部分；false）不裁剪图片，图片缩放显示，增加白色背景
     * @param int $cutPlace = 5  裁剪保留位置 1:左上, 2：中上， 3右上, 4：左中， 5：中中， 6：右中，7：左下， 8：中下，9右下
     * @param int $quality = 95  生成的缩略图质量
     * @return bool|string
     * @throws \wf\image\Exception
     */
    public function thumb($thumbWidth, $thumbHeight, $isCut = true, $cutPlace = 5, $quality = 95);
    
    /**
     * 给图片打水印
     * 建议用gif或png图片做水印，jpg不能设置透明，故不推荐用
     *
     * @param string $watermarkFile = 'static/images/watermark.png' 水印图片
     * @param int $watermarkPlace = 9 水印放置位置 1:左上, 2：中上， 3右上, 4：左中， 5：中中， 6：右中，7：左下， 8：中下，9右下
     * @param int $quality = 95 被打水印后的新图片(相对于打水印前)质量百分比
     * @return bool|string
     */
    public function watermark($watermarkFile = 'static/images/watermark.png', $watermarkPlace = 9, $quality = 95);
}

```


## TODO
- gif动态图打水印后返回动态缩略图。
- 增加支持使用ImageMagick库进行处理图片


<br />  
<br />  

### 要了解更多？  
> - [官方完整文档首页](http://docs.windwork.org/manual/)  
> - [官方源码首页](https://github.com/windwork)  
