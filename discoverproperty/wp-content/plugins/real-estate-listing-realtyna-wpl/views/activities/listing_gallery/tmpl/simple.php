<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/** set params **/
$wpl_properties = isset($params['wpl_properties']) ? $params['wpl_properties'] : array();
$this->property_id = isset($wpl_properties['current']['data']['id']) ? $wpl_properties['current']['data']['id'] : NULL;

/** get image params **/
$this->image_width = isset($params['image_width']) ? $params['image_width'] : 285;
$this->image_height = isset($params['image_height']) ? $params['image_height'] : 200;
$this->image_class = isset($params['image_class']) ? $params['image_class'] : '';
$this->resize = (isset($params['resize']) and trim($params['resize']) != '') ? $params['resize'] : 1;
$this->rewrite = (isset($params['rewrite']) and trim($params['rewrite']) != '') ? $params['rewrite'] : 0;
$this->watermark = (isset($params['watermark']) and trim($params['watermark']) != '') ? $params['watermark'] : 0;
$this->img_category = (isset($image['category']) and trim($image['category']) != '') ? $image['category'] : '';

/** Property tags **/
$features = '';
$hot_offer = '';
$open_house = '';
$forclosure = '';

if(isset($wpl_properties['current']['rendered'][400]) and $wpl_properties['current']['rendered'][400]) $features = '<div class="feature">'.$wpl_properties['current']['rendered'][400]['name'].'</div>';
if(isset($wpl_properties['current']['rendered'][401]) and $wpl_properties['current']['rendered'][401]) $hot_offer = '<div class="hot_offer">'.$wpl_properties['current']['rendered'][401]['name'].'</div>';
if(isset($wpl_properties['current']['rendered'][402]) and $wpl_properties['current']['rendered'][402]) $open_house = '<div class="open_house">'.$wpl_properties['current']['rendered'][402]['name'].'</div>';
if(isset($wpl_properties['current']['rendered'][403]) and $wpl_properties['current']['rendered'][403]) $forclosure = '<div class="forclosure">'.$wpl_properties['current']['rendered'][403]['name'].'</div>';

/** render gallery **/
$raw_gallery = isset($wpl_properties['current']['items']['gallery']) ? $wpl_properties['current']['items']['gallery'] : array();
$gallery = wpl_items::render_gallery($raw_gallery);
?>
<div class="wpl_gallery_container" id="wpl_gallery_container<?php echo $this->property_id; ?>">
    <?php
    if(!count($gallery))
    {
        echo '<div class="no_image_box"></div>';
    }
    else
    {
        $image_url = $gallery[0]['url'];
        
        if($this->resize and $this->image_width and $this->image_height and $this->img_category != 'external')
        {
            /** set resize method parameters **/
            $params = array();
            $params['image_name'] = $gallery[0]['raw']['item_name'];
            $params['image_parentid'] = $gallery[0]['raw']['parent_id'];
            $params['image_parentkind'] = $gallery[0]['raw']['parent_kind'];
            $params['image_source'] = $gallery[0]['path'];
            
            /** resize image if does not exist **/
            $image_url = wpl_images::create_gallary_image($this->image_width, $this->image_height, $params, $this->watermark, $this->rewrite);
        }
        
        echo '<img id="wpl_gallery_image'.$this->property_id .'" src="'.$image_url.'" class="wpl_gallery_image '.$this->image_class.'" alt="'.$params['image_name'].'" width="'.$this->image_width.'" height="'.$this->image_height.'" style="width: '.$this->image_width.'px; height: '.$this->image_height.'px;" />';
    }
	
    /* Property tags */
    echo $features.$hot_offer.$open_house.$forclosure;
    ?>
</div>