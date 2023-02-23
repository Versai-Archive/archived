<?php
namespace Jibix\Disguise\utils;
use Jibix\Disguise\Main;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;


/**
 * Class SkinUtils
 * @package Jibix\Disguise\utils
 * @author Jibix
 * @date 08.02.2022 - 23:44
 * @project Disguise
 */
class SkinUtils{

    /**
     * Function getSkinFromPng
     * @param string $resource
     * @param string $skinId
     * @param string|null $geometryName
     * @param string|null $customGeometry
     * @return Skin
     */
    public static function getSkinFromPng(string $resource, string $skinId, ?string $geometryName = null, ?string $customGeometry = null): Skin{
        $size = getimagesize($resource);
        if ($size[0] * $size[1] * 4 == 65536) {
            $img = self::resizeImage($resource, 128, 128);
        } else {
            $img = self::resizeImage($resource, 64, 64);
        }
        imagecolortransparent($img, imagecolorallocatealpha($img, 0, 0, 0, 127));
        $bytes = "";
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $rgba = @imagecolorat($img, $x, $y);
                $bytes .= chr(($rgba >> 16) & 0xff) . chr(($rgba >> 8) & 0xff) . chr($rgba & 0xff) . chr(((~($rgba >> 24)) << 1) & 0xff);
            }
        }
        @imagedestroy($img);
        if (empty($customGeometry)) {
            $skinData = new SkinData($skinId, "", '{"geometry":{"default":"geometry.humanoid.custom"}}', new SkinImage($size[1], $size[0], $bytes), [], new SkinImage(0, 0, ""), file_get_contents(Main::getInstance()->getDataFolder() . "/geometry.json"), "{}", false, false, false, "");
            return SkinAdapterSingleton::get()->fromSkinData($skinData);
        } else {
            return new Skin($skinId, $bytes, "", "geometry." . $geometryName, $customGeometry);
        }
    }

    /**
     * Function fromImage
     * @param string|resource $pathOrImage
     * @return string
     */
    public static function fromImage($pathOrImage): string{
        if (is_string($pathOrImage)) {
            $pathOrImage = @imagecreatefrompng($pathOrImage);
        }
        $bytes = "";
        for ($y = 0; $y < imagesy($pathOrImage); $y++) {
            for ($x = 0; $x < imagesx($pathOrImage); $x++) {
                $rgba = @imagecolorat($pathOrImage, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        @imagedestroy($pathOrImage);
        return $bytes;
    }

    /**
     * Function resizeImage
     * @param string $file
     * @param int $w
     * @param int $h
     * @return false|\GdImage|resource
     */
    public static function resizeImage(string $file, int $w, int $h){
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($w / $h > $r) {
            $newWidth = $h * $r;
            $newHeight = $h;
        } else {
            $newWidth = $w;
            $newHeight = $w / $r;
        }
        $img = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $dst;
    }
}