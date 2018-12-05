<?php 
/* 
Plugin Name: yandex_product 
Description: Плагин для отображение информации о товаре в соответствии со схемой Product и Offer
Version: 1.0
Author: Блакирев Яков
short_cod=[product_yndx]
*/ 

// привязываем функции сотворения метабокса и 
// сохранения данных к соответствующим хукам: 
add_action('add_meta_boxes', 'yandex_product_init'); 
add_action('save_post', 'yandex_product_save'); 

//script
function enqueue_media_uploader()
{
    wp_enqueue_media();
}

add_action("admin_enqueue_scripts", "enqueue_media_uploader");

// function theme_name_scripts() {
// 	wp_enqueue_style( 'style-name', plugins_url(). '/metatest/style.css' );
// 	wp_enqueue_script( 'newscript', plugins_url(). '/metatest/admin.js');
// 	}
// add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );
wp_enqueue_script('newscript', plugins_url(). '/yandex_product/admin.js',array( 'jquery' ));
wp_enqueue_style( 'style-name', plugins_url(). '/yandex_product/style.css' );

 //wp_enqueue_script('newscript', plugins_url(). '/metatest/admin.js');

function yandex_product_init() {

	 $screens = array( 'post', 'page' );
	 foreach ($screens as $screen) {
	 	add_meta_box('yandex_product', 'Yandex_product-параметр поста', 
'yandex_product_showup', $screen, 'side', 'default'); 
	 }

} 
function yandex_product_showup($post, $box) { 

// получение существующих метаданных

$meta_data_arr = get_post_meta($post->ID, null, true); 

$img_id=$meta_data_arr['_product_image_id'][0];

 $width=115;
$height=115;
wp_nonce_field('yandex_product_action', 'yandex_product_nonce'); 

// поле с именем товара
echo '<p>Название услуги\товара: <input type="text" name="product_name" value="' 
. esc_attr($meta_data_arr["_product_name"][0]) . '"/></p>';
echo '<p>Описание услуги\товара: <input type="text" name="product_description" value="' 
. esc_attr($meta_data_arr["_product_description"][0]) . '"/></p>';

echo my_image_uploader( $img_id, $width, $height );


echo '<p>Цена услуги\товара: <input type="number" name="product_price" value="' 
. esc_attr($meta_data_arr["_product_price"][0]) . '"/></p>';
echo show_list($meta_data_arr["_product_currency"][0]);
}

function show_list($curency)
{
	$res_str.='<p><select name="product_currency">';
	$arr=array('RUB','GRN','USD','EUR');
	for ($i=0; $i < 5; $i++) { 
		if(strcmp($curency,$arr[$i])!=0)
			$res_str.="<option  value=$arr[$i]>$arr[$i]</option>";
		else $res_str.="<option selected value=$arr[$i]>$arr[$i]</option>";
	}
	$res_str.='</select></p>';
	return $res_str;
}

function yandex_product_save($postID) { 

// Проверка пришли ли данные	 
if (!isset($_POST['product_name'])&&!isset($_POST['product_description'])&&!isset($_POST['product_image'])&&!isset($_POST['product_price'])&&!isset($_POST['product_currency'])) 
return; 

// не происходит ли автосохранение? 
if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
return; 

// не ревизию ли сохраняем? 
if (wp_is_post_revision($postID)) 
return; 

// проверка достоверности запроса 
check_admin_referer('metatest_action', 'metatest_nonce'); 

// коррекция данных 
$product_name = sanitize_text_field($_POST['product_name']);
$product_description = sanitize_text_field($_POST['product_description']);
$product_image = sanitize_text_field($_POST['product_image_id']);
$product_price = sanitize_text_field($_POST['product_price']);
$product_currency = sanitize_text_field($_POST['product_currency']); 

// запись


if(isset($product_name))
update_post_meta($postID, '_product_name', $product_name);
if(isset($product_description))
update_post_meta($postID, '_product_description', $product_description); 
if(isset($product_image))
{
	if(strcmp($product_image,'remove_img')!=0)
	{
		update_post_meta($postID, '_product_image_id', $product_image);
	}
	else
	{
		my_delete_post_meta($postID, '_product_image_id');
	}
} 
if(isset($product_price))
update_post_meta($postID, '_product_price', $product_price); 
if(isset($product_currency))
update_post_meta($postID, '_product_currency', $product_currency); 


} 



function my_image_uploader( $img_id, $width, $height ) {

    // Set variables  
    

    if ( !empty($img_id) ) {
        $image_attributes = wp_get_attachment_image_src( $img_id, array( $width, $height ) );
        $src = $image_attributes[0];
        $value = $img_id;
        echo '
        <div class="upload">
            <img src="' . $src . '" width="' . $width . 'px" height="' . $height . 'px" />
            <div>
                <input type="hidden" name="product_image_id" id="product_image_id" value="' . $value . '" />
                <a href="#" class="remove_image_link">Удалить изображение </a>
            </div>
        </div>
    ';
    } else {
       echo '
        <div class="upload">           
            <div>
                <input type="hidden" name="product_image_id" id="product_image_id" value="" />
                <a href="#" class="upload_image_link">Загрузить изображение товара/услуги </a>
            </div>
        </div>
    ';
    }
    
   
}

function my_delete_post_meta( $post_id, $meta_key, $meta_value = '' ) {
	// Make sure meta is added to the post, not a revision.
	if ( $the_post = wp_is_post_revision($post_id) )
		$post_id = $the_post;

	return delete_metadata('post', $post_id, $meta_key, $meta_value);
}

/*Widget*/


// Register and load the widget
function wpb_load_widget() {
    register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );
 
// Creating the widget 
class wpb_widget extends WP_Widget {
 
function __construct() {
parent::__construct(
 
// Base ID of your widget
'wpb_widget', 
 
// Widget name will appear in UI
__('YandexProduct Widget', 'yp_widget_domain'), 
 
// Widget description
array( 'description' => __( 'Widget for YandexProduct plugin', 'yp_widget_domain' ), ) 
);
}
 
// widget 
 
public function widget( $args, $instance ) {

global $post;

$meta_data_arr = get_post_meta($post->ID, null, true);

if(count($meta_data_arr)>0)
{
$title = apply_filters( 'widget_title', $instance['title'] );
 
// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];

echo show_product($meta_data_arr);

echo $args['after_widget'];
}
else echo "string";
}
         
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'wpb_widget_domain' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php 
}
     
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
return $instance;
}
}
//Вывод метаданных поста по схеме
function show_product($meta_data_arr)
{
if(count($meta_data_arr)>0):?>	
<div class="product" itemscope itemtype="http://schema.org/Product">

<?php if(!empty($meta_data_arr['_product_name'])):?>
  <h1 itemprop="name"><?php echo $meta_data_arr['_product_name'][0]?></h1>
<?php endif;?>

<?php if(!empty($meta_data_arr['_product_description'])):?>
  <span class="product_description" itemprop="description"><?php echo $meta_data_arr['_product_description'][0];?></span>
<?php endif;?>
<?php if(!empty($meta_data_arr['_product_image_id'])){
	$image_attributes = wp_get_attachment_image_src( $meta_data_arr['_product_image_id'][0], $size = 'thumbnail',$icon = false  );
	?>
  <img src="<?php echo $image_attributes[0];?>" itemprop="image">
<?php }?>
  <div itemprop="offers" itemscope itemtype="http://schema.org/Offer"> 

<?php if(!empty($meta_data_arr['_product_price'])):?>
    <span itemprop="price"><?php echo $meta_data_arr['_product_price'][0];?></span>
 <?php endif;?>   
<?php if(!empty($meta_data_arr['_product_currency'])):?>
    <span itemprop="priceCurrency"><?php echo $meta_data_arr['_product_currency'][0];?>.</span>
<?php endif;?> 
  </div>
</div>
<?php
endif;
}


function product_func( $atts ) {
if(!empty($atts['id']))
{
	$pot_id=$atts['id'];
	$meta_data_arr = get_post_meta($pot_id, null, true);
	if(count($meta_data_arr)>0)
	{
		echo show_product($meta_data_arr);
	}
}
else 
{
global $post;
$meta_data_arr = get_post_meta($post->ID, null, true);
if(count($meta_data_arr)>0)
	{
		echo show_product($meta_data_arr);
	}
}

}
add_shortcode( 'product_yndx', 'product_func' ); 
