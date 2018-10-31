<?php
/**
* 2010-2018 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2018 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class HotelImage extends ObjectModel
{
    public $id;
    public $id_hotel;
    public $hotel_image_id;
    public $cover;

    public static $definition = array(
        'table' => 'htl_image',
        'primary' => 'id',
        'fields' => array(
            'id_hotel' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'hotel_image_id' => array('type' => self::TYPE_STRING),
            'cover' => array('type' => self::TYPE_BOOL,'validate' => 'isBool')
        ),
    );

    /**
     * [getAllImagesByHotelId :: To get all images data of a hotel by hotel id]
     * @param  [int] $htl_id [Id of the hotel which images data you want]
     * @return [array|boolean] [if data found returns array containing information of the images of the hotel which id is passed]
     */
    public function getAllImagesByHotelId($htl_id)
    {
        return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'htl_image` WHERE `id_hotel` = '.(int)$htl_id);
    }

    /**
     * [deleteByHotelId :: To delete hotel's images data of a hotel by its hotel Id]
     * @param  [int] $htl_id [Id of the hotel which images data you want to delete]
     * @return [boolean]         [Returns true if deleted successfully else returns false]
     */
    public function deleteByHotelId($htl_id)
    {
        $delete = Db::getInstance()->delete('htl_image', '`id_hotel`='.(int)$htl_id);
        return $delete;
    }

    /**
     * [validAddHotelMainImage :: To validate the image of the hotel before saving it]
     * @param  [array] $image [variable having image information of the hotel]
     * @return [boolean]        [returns true if image is valid]
     */
    public static function validateImage($image)
    {
        if ($image['size'] > 0) {
            if ($image['tmp_name'] != "") {
                if (!ImageManager::isCorrectImageFileExt($image['name'])) {
                    return true;
                }
            }
        } else {
            return true;
        }
    }

    public static function getCover($idHotel)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'htl_image` WHERE `id_hotel` = '.(int)$idHotel.' AND `cover`=1'
        );
    }

    public function uploadHotelImages($images, $idHotel, $destPath)
    {
        if (isset($images) && $idHotel && $destPath) {
            $objHotelHelper = new HotelHelper();
            $hotelImages  = $images['tmp_name'];
            if (is_array($images['tmp_name'])) {
                foreach ($hotelImages as $image) {
                    $randName = $objHotelHelper->generateRandomCode(8);
                    $imageName = $randName.'.jpg';
                    if (ImageManager::resize($image, $destPath.$imageName)) {
                        $objHtlImage = new HotelImage();
                        $objHtlImage->id_hotel = $idHotel;
                        if ($coverImgExist = HotelImage::getCover($idHotel)) {
                            $objHtlImage->cover = 0;
                        } else {
                            $objHtlImage->cover = 1;
                        }
                        $objHtlImage->hotel_image_id = $randName;
                        $objHtlImage->save();
                    }
                }
            } else {
                $randName = $objHotelHelper->generateRandomCode(8);
                $imageName = $randName.'.jpg';
                if (ImageManager::resize($hotelImages, $destPath.$imageName)) {
                    $objHtlImage = new HotelImage();
                    $objHtlImage->id_hotel = $idHotel;
                    if ($coverImgExist = HotelImage::getCover($idHotel)) {
                        $objHtlImage->cover = 0;
                    } else {
                        $objHtlImage->cover = 1;
                    }
                    $objHtlImage->hotel_image_id = $randName;
                    if ($objHtlImage->save()) {
                        $addedImage = array(
                            'id_image' => $objHtlImage->id,
                            'cover' => $objHtlImage->cover,
                            'image_url' => _MODULE_DIR_.'hotelreservationsystem/views/img/hotel_img/'.
                            $randName.'.jpg',
                        );
                        return $addedImage;
                    }
                }
            }
            return true;
        }
        return false;
    }
}
