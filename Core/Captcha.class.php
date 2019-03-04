<?php
namespace Peak\Core;

class Captcha{
    // 随机字符串
    private $charset  = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
    // 验证码字符串
    private $code; 
    // 验证码长度
    private $codelen  = 4;
    // 宽度
    private $width    = 150;
    // 高度
    private $height   = 40;
    // 图形资源句柄              
    private $img;
    // 指定的字体                     
    private $font;
    // 指定字体大小                   
    private $fontsize = 20;
    // 指定字体颜色            
    private $fontcolor;               

    /**
     * 构造方法初始化
     * @param integer $codelen  验证码长度
     * @param integer $width    宽度
     * @param integer $height   高度
     * @param integer $fontsize 字体大小
     * @param string  $font     字体文件路径
     */
    public function __construct($codelen=4,$width=150,$height=40,$fontsize=20,$font="../Peak/Template/font/elephant.ttf"){
        $this->codelen  = $codelen;
        $this->width    = $width;
        $this->height   = $height;
        $this->fontsize = $fontsize;
        $this->font     = $font;
    }

    /**
     * 生成随机码
     */
    private function createCode() {
        $_len = strlen($this->charset)-1;
        for($i = 0; $i < $this->codelen; $i++) {
            $this->code .= $this->charset[mt_rand(0,$_len)];
        }
    }

    /**
     * 生成背景
     */
    private function createBg() {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color     = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
        imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
    }

    /**
     * 生成文字
     */
    private function createFont() {    
        $_x = $this->width / $this->codelen;
        for($i = 0; $i < $this->codelen; $i++) {
            $this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
        }
    }

    /**
     * 生成线条、雪花
     */
    private function createLine() {
        for($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
        }
        for($i=0;$i<100;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }
    }

    /**
     * 输出
     */
    private function outPut() {
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    /**
     * 对外生成验证码图片
     */
    public function generateCode() {
        $this->createBg();
        $this->createCode();
        $this->createLine();
        $this->createFont();
        $this->outPut();
    }

    /**
     * 获取验证码字符串
     */
    public function getCode() {
        return strtolower($this->code);
    }
}