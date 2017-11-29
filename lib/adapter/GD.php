<?php
/**
 * Windwork
 * 
 * 一个用于快速开发高并发Web应用的轻量级PHP框架
 * 
 * @copyright Copyright (c) 2008-2017 Windwork Team. (http://www.windwork.org)
 * @license   http://opensource.org/licenses/MIT
 */
namespace wf\image\adapter;

use \wf\image\Exception;

/**
 * 图片处理类，使用GD2生成缩略图和打水印 
 *
 * @package     wf.image.adapter
 * @author      cm <cmpan@qq.com>
 * @link        http://docs.windwork.org/manual/wf.image.html
 * @since       0.1.0
 */
class GD implements \wf\image\ImageInterface 
{
    /**
     * 图片相关信息
     * 
     * @var array
     */
    protected $imgInfo = '';
    
    /**
     * 图片二进制内容
     * @var string
     */
    protected $imageContent = '';
    
    /**
     * 缩略图背景颜色
     * @var array
     */
    protected $bgColor = [255, 255, 255];

    /**
     * @var string
     */
    protected $thumbType = 'jpg';
        
    /**
     * 构造函数中设置内存限制多一点以能处理较大图片
     * @throws \wf\image\Exception
     */
    public function __construct() 
    {
        if (!function_exists('gd_info')) {
            throw new Exception('你的php没有使用gd2扩展，不能处理图片');
        }
        @ini_set("memory_limit", "512M");  // 处理大图片的时候要较较大的内存
    }
    
    /**
     * {@inheritDoc}
     * @see \wf\image\ImageInterface::setImage()
     * @throws \wf\image\Exception
     */
    public function setImage($imageContent) 
    {
        if (!$imageContent || false == ($this->imgInfo = @getimagesizefromstring($imageContent))) {
            throw new Exception('错误的图片文件！');;
        }
        
        $this->imageContent = $imageContent;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \wf\image\ImageInterface::setThumbBgColor()
     */
    public function setBgColor($r, $g, $b) 
    {
        $this->bgColor = [$r, $g, $b];
    }
    
    /**
     * {@inheritDoc}
     * @see \wf\image\ImageInterface::thumb()
     */
    public function thumb($thumbWidth, $thumbHeight, $isCut = true, $cutPlace = 5, $quality = 95, $thumbType = 'jpg') 
    {
        if (!in_array($thumbType, ['jpg', 'png', 'webp'])) {
            throw new Exception('thumb image type must be webp|jpg|png');
        }
	
        if($isCut) {
            return $this->thumbCutOut($thumbWidth, $thumbHeight, $cutPlace, $quality, $thumbType);
        } else {
            return $this->thumbUnCut($thumbWidth, $thumbHeight, $quality, $thumbType);
        }
    }
    
    /**
     * 不裁剪方式生成缩略图
     * 
     * @param int $thumbWidth
     * @param int $thumbHeight
     * @return bool | string
     * @throws \wf\image\Exception
     */
    private function thumbUnCut($thumbWidth, $thumbHeight, $quality, $thumbType = 'jpg') 
    {
        list($srcW, $srcH) = $this->imgInfo;
        
        // 宽或高按比例缩放
        if ($thumbWidth == 0 || $thumbHeight == 0) {
            if ($thumbWidth == 0) {
                $thumbHeight = $srcH * ($thumbWidth / $srcW);
            } else {
                $thumbWidth = $thumbWidth = $srcW * ($thumbHeight/$srcH);
            }
            $imgW = $thumbWidth; // 图片显示宽
            $imgH = $thumbHeight; // 图片显示高
            $posX = 0; 
            $posY = 0;
        } else {
            if ($thumbWidth/$thumbHeight < $srcW/$srcH) {
                // 宽比例超过，补上高
                $imgW = $thumbWidth; // 图片显示宽
                $imgH = $thumbWidth * $srcH / $srcW; // 图片显示高
                $posX = 0;
                $posY = ($thumbHeight - $imgH) / 2;
            } else {
                // 高比例超过，补上宽
                $imgH = $thumbHeight; // 图片显示宽
                $imgW = $thumbHeight * $srcW / $srcH; // 图片显示高
                $posX = ($thumbWidth - $imgW) / 2;
                $posY = 0;
            }
        }
        
        $thumbImage = imagecreate($thumbWidth, $thumbHeight);
        
        // 填充背景色
        $fillColor = imagecolorallocate($thumbImage, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
        imagefill($thumbImage, 0, 0, $fillColor);

        // 合上图
        $attachImage = imagecreatefromstring($this->imageContent);
        imagecopyresized($thumbImage, $attachImage, $posX, $posY, 0, 0, $imgW, $imgH, $srcW, $srcH);
        
        // 为兼容云存贮设备，不直接把缩略图写入文件系统，而是返回文件内容
        ob_start();

        if ($thumbType == 'webp') {
            imagewebp($thumbImage, null, $quality);
        } elseif ($thumbType == 'png') {
            imagepng($thumbImage, null, floor($quality/10));
        } else {
            imagejpeg($thumbImage, null, $quality);
        }

        $thumb = ob_get_clean();

        imagedestroy($attachImage);
        imagedestroy($thumbImage);
        
        if(!$thumb) {
            throw new Exception('无法生成缩略图');
        }
        
        return $thumb;
    }
    
    /**
     * 裁剪方式生成缩略图
     * 
     * @param int $thumbWidth
     * @param int $thumbHeight
     * @param int $cutPlace = 5 1：x左y上, 2：x中y上， 3：x右y上, 4：x左y中， 5：x中y中， 6：x右y中，7：x左y下， 8：x中y下，9：x右y下 
     * @return bool | string
     * @throws \wf\image\Exception
     */
    private function thumbCutOut($thumbWidth, $thumbHeight, $cutPlace = 5, $quality = 95, $thumbType = 'jpg') 
    {
        list($srcW, $srcH) = $this->imgInfo;
        
        $imgH = $srcH;  // 取样图片高
        $imgW = $srcW;  // 取样图片宽
        $srcX = 0; // 取样图片x坐标开始值
        $srcY = 0; // 取样图片y坐标开始值
        
        $attachImage = imagecreatefromstring($this->imageContent);
                        
        if($thumbWidth == 0){
            // 宽等比例缩放
            $thumbWidth = $srcW * ($thumbHeight / $srcH);
        } elseif ($thumbHeight == 0) {
            // 高等比例缩放
            $thumbHeight = $srcH * ($thumbWidth / $srcW);            
        } else {
            // 需要裁剪              
            if((($thumbWidth / $imgW) * $imgH) > $thumbHeight) {
                // 高需要截掉
                $imgH = ($imgW / $thumbWidth) * $thumbHeight;
                
                // 高开始截取位置
                if (in_array($cutPlace, [4, 5, 6])) {
                    $srcY = ($srcH - $imgH)/2;
                } elseif (in_array($cutPlace, [7, 8, 9])) {
                    $srcY = $srcH - $imgH;
                }
            } else {
                // 宽需要截掉
                $imgW = ($imgH / $thumbHeight) * $thumbWidth;
                
                if (in_array($cutPlace, [2, 5, 8])) {
                    $srcX = ($srcW - $imgW)/2;
                } elseif (in_array($cutPlace, [3, 6, 9])) {
                    $srcX = $srcW - $imgW;
                }
            }
        }
        
        // 如果不设置缩略图保存路径则保存到原始文件所在目录
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        if($this->imgInfo['mime'] == 'image/gif') {
            imagecolortransparent($attachImage, imagecolorallocate($attachImage, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]));
        } else if($this->imgInfo['mime'] == 'image/png') {
            imagealphablending($thumbImage , false);//关闭混合模式，以便透明颜色能覆盖原画布
            imagesavealpha($thumbImage, true);
        }
        
        // 重采样拷贝部分图像并调整大小到$thumbImage
        imagecopyresampled($thumbImage, $attachImage ,0, 0, $srcX, $srcY, $thumbWidth, $thumbHeight, $imgW, $imgH);

        // 为兼容云存贮设备，这里不直接把缩略图写入文件系统
        ob_start();

        if ($thumbType == 'webp') {
            imagewebp($thumbImage, null, $quality);
        } elseif ($thumbType == 'png') {
            imagepng($thumbImage, null, floor($quality/10));
        } else {
            imagejpeg($thumbImage, null, $quality);
        }

        $thumb = ob_get_clean();

        imagedestroy($attachImage);
        imagedestroy($thumbImage);
        
        if(!$thumb) {
            throw new Exception('无法生成缩略图');
        }
        
        return $thumb;
    }

    /**
     * {@inheritDoc}
     * @see \wf\image\ImageInterface::watermark()
     */
    public function watermark($watermarkFile = 'static/images/watermark.png', $watermarkPlace = 9, $quality = 95) 
    {
        @list($imgW, $imgH) = $this->imgInfo;
        
        $watermarkInfo    = @getimagesize($watermarkFile);
        $watermarkLogo    = ('image/png' == $watermarkInfo['mime']) ? @imagecreatefrompng($watermarkFile) : @imagecreatefromgif($watermarkFile);

        if(!$watermarkLogo) {
            return;
        }

        list($logoW, $logoH) = $watermarkInfo;
        $wmwidth = $imgW - $logoW;
        $wmheight = $imgH - $logoH;

        if(is_readable($watermarkFile) && $wmwidth > 10 && $wmheight > 10) {
            switch($watermarkPlace) {
                case 1:
                    $x = +5;
                    $y = +5;
                    break;
                case 2:
                    $x = ($imgW - $logoW) / 2;
                    $y = +5;
                    break;
                case 3:
                    $x = $imgW - $logoW - 5;
                    $y = +5;
                    break;
                case 4:
                    $x = +5;
                    $y = ($imgH - $logoH) / 2;
                    break;
                case 5:
                    $x = ($imgW - $logoW) / 2;
                    $y = ($imgH - $logoH) / 2;
                    break;
                case 6:
                    $x = $imgW - $logoW;
                    $y = ($imgH - $logoH) / 2;
                    break;
                case 7:
                    $x = +5;
                    $y = $imgH - $logoH - 5;
                    break;
                case 8:
                    $x = ($imgW - $logoW) / 2;
                    $y = $imgH - $logoH - 5;
                    break;
                case 9:
                    $x = $imgW - $logoW - 5;
                    $y = $imgH - $logoH - 5;
                    break;
            }

            $dstImage = imagecreatetruecolor($imgW, $imgH);
            imagefill($dstImage, 0, 0, imagecolorallocate($dstImage, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]));            
            $targetImage = @imagecreatefromstring($this->imageContent);
            
            imageCopy($dstImage, $targetImage, 0, 0, 0, 0, $imgW, $imgH);
            imageCopy($dstImage, $watermarkLogo, $x, $y, 0, 0, $logoW, $logoH);

            ob_start();
            imagejpeg($dstImage, null, $quality);
            $ret = ob_get_clean();

            return $ret;
        }
    }


    /**
     * 获取GD库版本
     */
    public function getExtVersion() 
    {
        if(!function_exists('gd_info')) {
            return false;
        }
        
        $gdInfo = gd_info();
        return 'GD ' . $gdInfo['GD Version'];
    }
}