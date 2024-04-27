<?php
/*  
 * Functions file for Kapee child
 */

/*
 * Enqueue script and styles
 */
//add_action( 'wp_enqueue_scripts', 'kapee_child_enqueue_styles', 10010 );
function kapee_child_enqueue_styles() {
    $theme   = wp_get_theme( 'Kapee' );
    $version = $theme->get( 'Version' );
    wp_enqueue_style( 'kapee-child-style', get_stylesheet_directory_uri().'/style.css',array( 'kapee-style', 'kapee-basic' ), $version );
}
require_once 'custom-tablepress-price-v1.php';

function dd($p){
	echo "<pre>";
  print_r($p);
   echo "</pre>";
}

add_action( 'woocommerce_before_calculate_totals', 'before_calculate_totals', 10, 1 );
function before_calculate_totals( $cart_obj ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    echo "<pre>";
    //print_r($cart_obj);
    echo "</pre>";

$product_list = array();

foreach ($cart_obj->get_cart() as $key => $value) {
    
    $color_options = $value['thwepof_options']['printing_option']['value'];
    $product_id = $value['product_id'];
    $quantity = $value['quantity'];

    // Initialize product_list if not exists
    if (!isset($product_list['ids'])) {
        $product_list['ids'] = [];
    }

    // Check if product_id exists in product_list
    if (array_key_exists($product_id, $product_list['ids'])) {
        // Check if color_options exist for the product_id
        if (array_key_exists($color_options, $product_list['ids'][$product_id])) {
            // Increment quantity for the specific color option
            $upgrade = $product_list['ids'][$product_id][$color_options] + $quantity;
            $product_list['ids'][$product_id][$color_options] = $upgrade;
        } else {
            // Set quantity for the new color option
            $product_list['ids'][$product_id][$color_options] = $quantity;
        }
    } else {
        // Set quantity for the new product_id and color option
        $product_list['ids'][$product_id][$color_options] = $quantity;
    }
	
}

//dd($product_list);
// Rest of your code...


  /******************
 * 
 * Checking cart products if Are Grouped products End line
 * 
 * *********************** */

    

    foreach( $cart_obj->get_cart() as $key=>$value ) {

        $productId = $value['product_id'];
		
        $printing_string = $value['thwepof_options']['printing_option']['value'];

        if (array_key_exists($productId, $product_list['ids'])) {

             $updated_qty=$product_list['ids'][$productId][$printing_string];
           
        }
             
        
        $table_id = get_post_meta($productId, 'table_id', true);
        
       // $original_price = $value['data']->get_price();
        if($table_id) {
           $custome_price_data = get_quantity_group_price($table_id,$updated_qty,$printing_string);
         
           $value['data']->set_price( $custome_price_data );

        }
        
		  
    }

//dd($_POST);
//dd($cart_obj);
    
}

// *********************** Woocommerce Changes *********************////

add_action( 'wp_footer', 'add_product_ajax_add_to_cart_script' );
function add_product_ajax_add_to_cart_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
			
				function checkproducts() {
	    
					var prod = []; // Reset prod array before collecting data
					
					// Loop through each row with the class name 'productRow'
					document.querySelectorAll('.productRow').forEach(function(row) {
						
						// Loop through each element with the class name 'product_cart_data' within the row
						row.querySelectorAll('.product_cart_data').forEach(function(element) {
							if (element.value > 0) {
								
								var printing_option = document.getElementById(element.getAttribute('data-parent')).value;
								var product = {
									tap:printing_option,
									color: element.getAttribute('data-color'),
									size: element.getAttribute('data-size'),
									quantity: element.value
								};
								prod.push(product);
							}
						});
					});
					return prod;
					//console.log(prod); // Log the collected product data
				}
				
				function countproducts() {
	    
					var prods = []; // Reset prod array before collecting data
					
					// Loop through each row with the class name 'productRow'
					document.querySelectorAll('#productRow .produts_rows').forEach(function(row) {
						
						//console.log(row);
						//console.log(row.querySelector('.printing_options').value);
						var product = {
									tap:row.querySelector('.printing_options').value,
									color: row.querySelector('.product_color').value,
									size:row.querySelector('.product_size').value ,
									quantity: row.querySelector('.order_qty').value
								};
								prods.push(product);
						// Loop through each element with the class name 'product_cart_data' within the row
						/**row.querySelectorAll('.product_cart_data').forEach(function(element) {
							if (element.value > 0) {
								
								var printing_option = document.getElementById(element.getAttribute('data-parent')).value;
								var product = {
									tap:printing_option,
									color: element.getAttribute('data-color'),
									size: element.getAttribute('data-size'),
									quantity: element.value
								};
								prods.push(product);
							}
						});
						**/
						
						
					});
					return prods;
					//console.log(prod); // Log the collected product data
				}
				
				
			
			// Show the form when the "Add to Cart" button is clicked
				$('#close_form').click(function() {
					// Hide the form initially
				   //$('#product_form').hide();
				});
				// Hide the form initially
				//$('#product_form').hide();

				// Show the form when the "Add to Cart" button is clicked
				$('#show_product_form').click(function() {
					$('#product_form').show();
					
				});
				/**
			$('.variations_form').on('submit', function(e) {
                  e.preventDefault(); // Prevent the default form submission
        
                  // Serialize form data
				  var formData = $(this).serialize();
				  
				  console.log(formData);
				  
				  return;
				  });	
				  **/
			$('#mkdata').on('click', function(e) {
				
				ap=countproducts();
				console.log(ap);
			});	
            $('#add_all_ajax').on('click', function(e) {
                e.preventDefault();
                var product_id = $(this).data('product_id');
				 allproduct=countproducts();
				//$('.variations_form').submit();	
                  console.log(allproduct, product_id);
				  
			              
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    data: {
                        action: 'all_add_to_cart',
                        product_id: product_id,
						product_data:allproduct,
						
                    },
                    success: function(response) {
						console.log(response);
						//document.querySelector('.custom-add-to-cart-button').classList.add('testing');
						//$('#wpcf7-f6-p7-o1 .wpcf7-form').submit();
                        // Handle success response
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                    }
                });
            });
			
			
			
			
        });
    </script>
    <?php
}

add_action( 'wp_ajax_all_add_to_cart', 'all_add_to_cart' );
add_action( 'wp_ajax_nopriv_all_add_to_cart', 'all_add_to_cart' );

function all_add_to_cart() {
    // Include WooCommerce functions.
    if ( ! function_exists( 'WC' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        include_once( ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php' );
    }
    //print_r($_POST);
    // Retrieve product data from the AJAX call.
    $product_id = $_POST['product_id'];
    $product_data = $_POST['product_data'];
    $product_taps = array();

    // Loop through each product data
    foreach ( $product_data as $ky => $pdata ) {
        // Extract product data
       $attribute_color = $pdata['color'];
       $attribute_size  = $pdata['size'];
        $quantity        = $pdata['quantity'];
        $tap             = $pdata['tap'];

        // Check if product exists.
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            continue;
        }

        // Initialize variable to store variation ID
        $variation_id = 0;

        // Loop through each variation
        foreach ( $product->get_available_variations() as $variation ) {
			
            
			if (isset( $variation['attributes']['attribute_pa_farve'] ) && $variation['attributes']['attribute_pa_farve'] == $attribute_color ) {
                $variation_id = $variation['variation_id'];
                break;
            }
			
        }

        // Check if variation ID is found
        if ( $variation_id ) {
            // Check if the product with the same printing option and variation ID exists in the cart
            $existing_cart_item_key = find_existing_cart_item_key( $product_id, $variation_id, $tap );
			
            if ( $existing_cart_item_key ) {
                // Update the quantity of the existing cart item
                $updated_quantity = WC()->cart->set_quantity( $existing_cart_item_key, $quantity, true );
                if ( $updated_quantity ) {
                    // Store cart item key and tap data
                    $product_taps[] = array(
                        'cart_item_key' => $existing_cart_item_key,
                        'tap'           => $tap,
                    );
                }
            } else {
                // Define cart item data.
				 // Add the unique key to cart item data
				
                $cart_item_data = array(
                    'attribute_pa_farve' => $attribute_color,
                    'attribute_pa_size'  => $attribute_size,
                    'attribute_tap'   => $tap,
                    'thwepof_product_fields' => 'printing_option', // Assuming 'thwepof_product_fields' is a custom field
                    'printing_option' => $tap,
					
                );
				dd($cart_item_data);			
				add_filter('woocommerce_add_cart_item_data', 'generate_unique_cart_item_key', 10, 3);
							
				                // Add the product to the cart with a unique cart key
                $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $cart_item_data );
				//echo $cart_item_key; 
                // Check if the product was added successfully
                if ( $cart_item_key ) {
                    // Store cart item key and tap data
                    $product_taps[] = array(
                        'cart_item_key' => $cart_item_key,
                        'tap'           => $tap,
                    );
                }
            }
        } else {
            // Display an error message if variation ID is not found
			echo "data";
            wc_add_notice(__('Invalid variation. Please select valid options.', 'your-text-domain'), 'error');
        }
    }

    // Store the cart item keys and tap data in session or user meta
    // You can store it in session or user meta to access it on the cart page.
    // Example to store in session:
    session_start();
    $_SESSION['product_taps'] = $product_taps;

    // Redirect to the cart page after adding products
    wp_safe_redirect( wc_get_cart_url() );
    exit;
}

function find_existing_cart_item_key( $product_id, $variation_id, $printing_option ) {
    // Get the current cart contents
    $cart_items = WC()->cart->get_cart();

    // Loop through each cart item and compare printing option value and variation ID
    foreach ($cart_items as $cart_item_key => $cart_item) {
		
        if (isset($cart_item['variation_id']) && $cart_item['variation_id'] === $variation_id &&
            isset($cart_item['thwepof_options']['printing_option']['value']) &&
            $cart_item['thwepof_options']['printing_option']['value'] === $printing_option) {
				//dd($cart_item_key);
            return $cart_item_key;
        }else {
			//echo "not matched";
		}
    }

    // Return false if no matching cart item is found
    return false;
}
/*
function generate_unique_cart_key($cart_item_data, $product_id, $variation_id) {
    // Check if the cart item has thwepof_options and printing_option value
    if (isset($cart_item_data['thwepof_options']['printing_option']['value'])) {
        // Generate a unique key based on printing_option value, product ID, and variation ID
        $printing_option_value = $cart_item_data['thwepof_options']['printing_option']['value'];
        $unique_key = md5($product_id . $variation_id . $printing_option_value);
        
        // Append the unique key to cart_item_data
        $cart_item_data['unique_key'] = $unique_key;
    }
    
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'generate_unique_cart_key', 10, 3);
**/
// Remove all cart data
function remove_all_cart_data() {
    WC()->cart->empty_cart();
}

// Hook the function to an action
//add_action('init', 'remove_all_cart_data');


function generate_unique_cart_item_key($cart_item_data, $product_id, $variation_id) {
    // Generate a unique key
    $unique_key = md5( microtime() . rand() );

    // Add the unique key to the cart item data
    $cart_item_data['unique_key'] = $unique_key;

    return $cart_item_data;
}

function update_custom_field_in_cart( $cart ) {
    

	session_start();

    // Retrieve stored tap data if session exists
    if ( isset( $_SESSION['product_taps'] ) ) {
        $product_taps = $_SESSION['product_taps'];
		
    // Loop through cart items.
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        // Get product ID.
        $product_id = $cart_item['product_id'];
		
		//echo "<pre>";
		//print_r($cart_item);
		 //echo "</pre>";
        // Get current value of the custom field.
        $current_value = isset( $cart_item['thwepof_options']['printing_option']['value'] ) ? $cart_item['thwepof_options']['printing_option']['value'] : '';
		foreach ( $product_taps as $item ) {
			 // Update the custom field value if needed.
       
            $cart_item['thwepof_options']['printing_option']['value'] = $item['tap'];
            $cart->cart_contents[ $item['cart_item_key'] ]['thwepof_options']=array(
            'printing_option' => array(
                'name' => 'printing_option',
                'value' => $item['tap'],
                'type' => 'select',
                'label' => 'Tryk Muligheder',
                'options' => array(
                    'Uden tryk' => 'Uden tryk',
                    'Med 1 farvet tryk' => 'Med 1 farvet tryk',
                    'Med 2 farvet tryk' => 'Med 2 farvet tryk',
                    'Med 3 farvet tryk' => 'Med 3 farvet tryk',
                    'Med 4 farvet tryk' => 'Med 4 farvet tryk',
                    'Med 5 farvet tryk' => 'Med 5 farvet tryk',
                    'Med 6 farvet tryk' => 'Med 6 farvet tryk',
                    'Begge sider Med 1 farvet tryk' => 'Begge sider Med 1 farvet tryk',
                    'Begge sider Med 2 farvet tryk' => 'Begge sider Med 2 farvet tryk',
                    'Begge sider Med 3 farvet tryk' => 'Begge sider Med 3 farvet tryk',
                    'Begge sider Med 4 farvet tryk' => 'Begge sider Med 4 farvet tryk',
                    'Begge sider Med 5 farvet tryk' => 'Begge sider Med 5 farvet tryk',
                    'Begge sider Med 6 farvet tryk' => 'Begge sider Med 6 farvet tryk',
                ),
            ),
        );
        
		
		}
       
    }
}

unset( $_SESSION['product_taps'] );
}
add_action( 'woocommerce_before_calculate_totals', 'update_custom_field_in_cart', 10, 1 );


function create_pop_form_data($product_id) {
	
	 // Get the product object
    
    // Get the product object
    $product = wc_get_product( $product_id );
    
    // Check if the product is variable
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        echo "Product not found or not variable.";
        return;
    }
    //dd($product);
    // Initialize arrays for attributes
    $psize = $pcolor = $ptap = array();
    
    // Get all attributes
    $attributes = $product->get_attributes();
    
	//dd( $attributes);
    // Loop through each attribute
    foreach ($attributes as $attribute) {
        // Get attribute name
        $attribute_name = $attribute->get_name();
        
        // Get attribute values
        $attribute_values = $attribute->get_options();
        
        // Store attribute values based on name
        switch ($attribute_name) {
            case 'pa_size':
                $psize = array();
				
				foreach($attribute_values as $sizeval){
					
					// Define the attribute ID
					$attribute_id = $sizeval; // Replace with the ID of the attribute you want to retrieve

					// Get the term object by ID
					 $attribute = get_term($attribute_id, 'pa_size'); // 'pa_farve' is the taxonomy name for product attributes

					// Check if the term object exists and is not an error
					if ($attribute && !is_wp_error($attribute)) {
						// Get the attribute name
						$psize[] = $attribute->name;
						// Get the attribute slug
						//$attribute_slug = $attribute->slug;
						// Get the attribute description
						//$attribute_description = $attribute->description;
						
						// Output the attribute details
						//echo "Name: $attribute_name, Slug: $attribute_slug, Description: $attribute_description";
					} 
				}
								 
										
					
									
				
				//print_r($psize);
                break;
            case 'pa_farve':
                $pcolor = array();
				$total=0;
				foreach($attribute_values as $sizeval){
					
					// Define the attribute ID
					$attribute_id = $sizeval; // Replace with the ID of the attribute you want to retrieve

					// Get the term object by ID
					 $attribute = get_term($attribute_id, 'pa_farve'); // 'pa_farve' is the taxonomy name for product attributes

					// Check if the term object exists and is not an error
					if ($attribute && !is_wp_error($attribute)) {
						// Get the attribute name
						$pcolor[$total]['name'] = $attribute->name;
						// Get the attribute slug
						$pcolor[$total]['slug'] = $attribute->slug;
						// Get the attribute description
						//$attribute_description = $attribute->description;
						
						// Output the attribute details
						//echo "Name: $attribute_name, Slug: $attribute_slug, Description: $attribute_description";
					} 
					
					$total++;
				}
				
                break;
            case 'tap':
                $ptap = $attribute_values;
                break;
            default:
                // Handle other attributes if needed
                break;
        }
    }
    
    // Generate Tryk Muligheder select box
    $tryk_select = '<select id="printing_options_1" name="printing_option" value="" class="thwepof-input-field"><option value="Uden tryk">Uden tryk</option><option value="Med 1 farvet tryk">Med 1 farvet tryk</option><option value="Med 2 farvet tryk">Med 2 farvet tryk</option><option value="Med 3 farvet tryk">Med 3 farvet tryk</option><option value="Med 4 farvet tryk">Med 4 farvet tryk</option><option value="Med 5 farvet tryk">Med 5 farvet tryk</option><option value="Med 6 farvet tryk">Med 6 farvet tryk</option><option value="Begge sider Med 1 farvet tryk">Begge sider Med 1 farvet tryk</option><option value="Begge sider Med 2 farvet tryk">Begge sider Med 2 farvet tryk</option><option value="Begge sider Med 3 farvet tryk">Begge sider Med 3 farvet tryk</option><option value="Begge sider Med 4 farvet tryk">Begge sider Med 4 farvet tryk</option><option value="Begge sider Med 5 farvet tryk">Begge sider Med 5 farvet tryk</option><option value="Begge sider Med 6 farvet tryk">Begge sider Med 6 farvet tryk</option></select>';
    
    // Generate Size table row
    $size_row = "<tr><td></td>";
    foreach ($psize as $size) {
        $size_row .= "<td>$size</td>";
    }
    $size_row .= "</tr>";
    
    // Generate Color table rows with input fields
    $color_rows = "";
	//dd($pcolor);
    foreach ($pcolor as $colorkey => $color) {
		
	//echo ($color['name']);
        $color_rows .= "<tr><td>{$color['name']}</td>";

        foreach ($psize as $size) {
            $color_rows .= "<td><input class='product_cart_data' data-parent='printing_options_1' data-tap='' data-color='{$color['slug']}' data-size='$size' type='text' name='printing_options[1][{$color['slug']}][$size]'></td>";

        }
        $color_rows .= "</tr>";
    }
    
    // Output the tables
    echo "<div id='product_form'><span id='close_form'><button class='pswp__button pswp__button--close' aria-label='Luk (Esc)'></button></span><div class='popcontainer'>";
    echo "<table class='productRow'>";
    echo "<tr><td>Tryk Muligheder</td><td>$tryk_select</td></tr>";
    echo $size_row;
    echo $color_rows;
    echo "</table>";
    echo "<button data-product_id='$product_id' id='add_all_ajax'> Add to custom Cart</button>";
    echo "<input type='hidden' name='product_id' value='$product_id'>";
    echo "</div></div>";
}

// Hook this function to an action that runs on the cart page or wherever you want to display the form
//add_action( 'init', 'create_pop_form_data' );
function display_pop_form_data() {
     if ( is_product() ) {
        global $product; // Make the $product variable available within this function
        
        // Check if $product is set and is a WC_Product object
        if ( isset($product) && is_a($product, 'WC_Product') ) {
            // Get the product ID
            $product_id = $product->get_id();
            
            // Now you can use $product_id to get the product details
            
            // Your form creation function here
            //create_pop_form_data($product_id);
			//create_pop_form_data_updated($product_id);
        }
    }
	
			// Define the product ID
		
			
			
	
}

add_action( 'woocommerce_single_product_summary', 'display_pop_form_data' );

// Add the "More Product Options" button
function add_more_product_options_button() {
    global $product;
    if ( $product && $product->is_type('variable') ) {
        echo '<button id="show_product_form">More Product Options</button>';
    }
}
add_action( 'woocommerce_before_add_to_cart_form', 'add_more_product_options_button', 15 );

add_action( 'woocommerce_before_add_to_cart_form', 'create_pop_form_data_updated', 15 );



function create_pop_form_data_updated($product_id) {
    // Get the product object
	
	 if ( is_product() ) {
        global $product; // Make the $product variable available within this function
        
        // Check if $product is set and is a WC_Product object
        if ( isset($product) && is_a($product, 'WC_Product') ) {
            // Get the product ID
            $product_id = $product->get_id();
            
            // Now you can use $product_id to get the product details
            
            // Your form creation function here
            //create_pop_form_data($product_id);
			//create_pop_form_data_updated($product_id);
        }
    }
    $product = wc_get_product($product_id);
    
    // Check if the product is variable
    if (!$product || !$product->is_type('variable')) {
        echo "Product not found or not variable.";
        return;
    }

    // Initialize arrays for attributes
    $psize = $pcolor = array();

    // Get all attributes
    $attributes = $product->get_attributes();

    // Loop through each attribute
    foreach ($attributes as $attribute) {
        // Get attribute name
        $attribute_name = $attribute->get_name();
        
        // Get attribute values
        $attribute_values = $attribute->get_options();
        
        // Store attribute values based on name
        switch ($attribute_name) {
            case 'pa_size':
                foreach ($attribute_values as $sizeval) {
                    // Define the attribute ID
                    $attribute_id = $sizeval;

                    // Get the term object by ID
                    $term = get_term($attribute_id, 'pa_size');

                    // Check if the term object exists and is not an error
                    if ($term && !is_wp_error($term)) {
                        // Get the attribute name
                        $psize[] = $term->name;
                    }
                }
                break;
            case 'pa_farve':
                foreach ($attribute_values as $colorval) {
                    // Define the attribute ID
                    $attribute_id = $colorval;

                    // Get the term object by ID
                    $term = get_term($attribute_id, 'pa_farve');

                    // Check if the term object exists and is not an error
                    if ($term && !is_wp_error($term)) {
                        // Get the attribute name and slug
                        $pcolor[] = array(
                            'name' => $term->name,
                            'slug' => $term->slug
                        );
                    }
                }
                break;
            case 'tap':
                // Handle tap attribute if needed
                break;
            default:
                // Handle other attributes if needed
                break;
        }
    }

    // Generate dropdown options for taps
    $tap_options = '<select class="printing_options" name="tap_option"><option value="Tap 1">Tap 1</option><option value="Tap 2">Tap 2</option><option value="Tap 3">Tap 3</option></select>';
	$tryk_select = '<select id="printing_options_1" name="printing_option" value="" class="printing_options thwepof-input-fields"><option value="Uden tryk">Uden tryk</option><option value="Med 1 farvet tryk">Med 1 farvet tryk</option><option value="Med 2 farvet tryk">Med 2 farvet tryk</option><option value="Med 3 farvet tryk">Med 3 farvet tryk</option><option value="Med 4 farvet tryk">Med 4 farvet tryk</option><option value="Med 5 farvet tryk">Med 5 farvet tryk</option><option value="Med 6 farvet tryk">Med 6 farvet tryk</option><option value="Begge sider Med 1 farvet tryk">Begge sider Med 1 farvet tryk</option><option value="Begge sider Med 2 farvet tryk">Begge sider Med 2 farvet tryk</option><option value="Begge sider Med 3 farvet tryk">Begge sider Med 3 farvet tryk</option><option value="Begge sider Med 4 farvet tryk">Begge sider Med 4 farvet tryk</option><option value="Begge sider Med 5 farvet tryk">Begge sider Med 5 farvet tryk</option><option value="Begge sider Med 6 farvet tryk">Begge sider Med 6 farvet tryk</option></select>';

    // Generate dropdown options for size
    $size_options = '<select class="product_size"  name="size_option">';
    foreach ($psize as $size) {
        $size_options .= "<option value='$size'>$size</option>";
    }
    $size_options .= '</select>';

    // Generate dropdown options for color
    $color_options = '<select  class="product_color" name="color_option">';
    foreach ($pcolor as $color) {
        $color_options .= "<option value='{$color['slug']}'>{$color['name']}</option>";
    }
    $color_options .= '</select>';

    // Generate quantity input field
    $quantity_input = '<input class="order_qty" type="number" name="quantity" value="1" min="1">';

    // Output the table row
    echo "<div id='product_form'><span style='display:none;' id='close_form'><button class='pswp__button pswp__button--close' aria-label='Luk (Esc)'></button></span><div class='popcontainer'>";
    echo "<table id='productRow' class='productRow'>";
    echo "<tr><td>Tap</td><td>Size</td><td>Color</td><td>Quantity</td><td>Notes</td></tr>";
    echo "<tr class='produts_rows'><td>$tryk_select</td><td>$size_options</td><td>$color_options</td><td>$quantity_input</td><td>note</td></tr>";
    echo "</table>";
	 echo "<button id='addRowBtn'>Add Row</button>";
    echo "<button data-product_id='$product_id' id='add_all_ajax'> Add to custom Cart</button>";
	echo "<button data-product_id='$product_id' id='mkdata'> Create Data</button>";
    echo "<input type='hidden' name='product_id' value='$product_id'>";
    echo "</div></div>";
// JavaScript for adding rows
    ?>
    <script>
	
		document.addEventListener('DOMContentLoaded', function () {
            const addRowBtn = document.getElementById('addRowBtn');
            const productRow = document.getElementById('productRow');

             let rowCount = 1;

			addRowBtn.addEventListener('click', function () {
				const newRow = productRow.insertRow(-1);
				rowCount++;
				newRow.id = 'row' + rowCount;
				//newRow.class = 'produts_rows';
				newRow.classList.add('produts_rows');
				newRow.innerHTML = productRow.rows[1].innerHTML; // Copy the structure of the first row

				// Reset values in the new row
				newRow.querySelectorAll('select, input').forEach(function (element) {
					if (element.tagName === 'SELECT') {
						element.selectedIndex = 0;
					} else if (element.tagName === 'INPUT') {
						element.value = '';
					}
				});
			});
        });
    
        
    </script>
    <?php
}





























































































/********************************************************************* 
                        Cutom Code
**********************************************************************/

// add_filter( 'woocommerce_email_order_items_args', 'woocommerce_email_order_items_args_func', 10, 1 );
// function woocommerce_email_order_items_args_func( $order_items_args ) {
//     return $order_items_args;
// }

/*
add_action( 'woocommerce_before_calculate_totals', 'before_calculate_totals', 10, 1 );
function before_calculate_totals( $cart_obj ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

// if ($_GET['dev']==1) {

    // Category 1 (S): Børnetøj , Kasketter , Muleposer , T-Shirts
    $c1cats = array( 132, 138, 140, 127 );
    // $c1prices = array( 
    //     // Børnetøj
    //     '40500'=>30, '40510'=>35, '40552'=>36, '40634'=>75, '40636'=>95, '40638'=>105, '40836'=>200, '42030'=>39,
    //     // Kasketter
    //     '36'=>40, '42'=>34, '44'=>40, '052'=>34, '054'=>34, '066'=>34, '068'=>34, '500'=>34,
    //     // Muleposer
    //     '1510'=>25, '1840'=>30, '6602'=>20, '6606'=>23, '6619'=>25, '6623'=>25, '6649'=>30, '6668'=>28, '6670'=>20, '7201'=>25,
    //     '7202'=>30, '7203'=>25, '7310'=>30, '7311'=>30, '7313'=>32, '7315'=>27, '7316'=>29,
    //     // T-Shirts
    //     '300'=>50,    '302'=>55,    '310'=>42,    '311'=>58,    '312'=>45,    '313'=>50,    '315'=>47,   '317'=>40,   '370'=>49,   '371'=>49,
    //     '372'=>49,    '373'=>49,    '0500'=>34,    '0501'=>35,    '502'=>38,    '506'=>47,    '508'=>47,   '0509'=>60,   '0510'=>40,   '0512'=>38,
    //     '514--1'=>38,    '0517'=>47,    '0518'=>60,    '528'=>58,    '529'=>58,    '536'=>47,    '537'=>47,   '538'=>40,   '539'=>40,   '540'=>40,
    //     '541'=>40,    '542'=>40,    '543'=>40,    '546'=>70,    '550'=>40,    '0552'=>42,    '0553'=>42,   '590'=>45,   '0591'=>55,   '0592'=>40,
    //     '594'=>45,    '595'=>43,    '1900'=>40,   '2000'=>32,   '2030'=>39,   '2032'=>39,   '40500'=>30, '40510'=>35, '40552'=>36, '42030'=>39,
    //     'G11020'=>81, 'G11021'=>95, 'G11024'=>81, 'G21020'=>81, 'G21021'=>95, 'G21024'=>81, 'S620'=>55,  'S630'=>55,
    // ); // id=>price

    // Category 2 (L): Fleece , Jakker , Skjorter , Softshell , Veste
    $c2cats = array( 134, 133, 129, 137, 135 );
    // $c2prices = array( 
    //     // Fleece
    //     '803'=>150, '805'=>140, '806'=>140, '807'=>140, '816'=>150, '818'=>220, '819'=>220, '826'=>240, '827'=>240, '828'=>240,
    //     '829'=>240, '847'=>220, '848'=>220, '850'=>250, '851'=>250,
    //     // Jakker
    //     '702'=>170,  '704'=>234,    '705'=>234,    '710'=>125,    '720'=>280,    '721'=>280,    '730'=>270,    '731'=>270,    '768'=>430,    '769'=>430,
    //     '773'=>320,  '774'=>320,    '814'=>250,    '815'=>250,    '830'=>240,    '831'=>240,    '836'=>290,    '886'=>250,    '887'=>250,    '898'=>370,
    //     '899'=>370,  'G11012'=>190, 'G11030'=>300, 'G11054'=>280, 'G11070'=>375, 'G21012'=>190, 'G21030'=>300, 'G21054'=>280, 'G21070'=>375, 'S900'=>390,
    //     'S910'=>390,
    //     // Skjorter
    //     '200'=>140,   '201'=>140,   'S40'=>170,   'S50'=>170,  'S51'=>170,   'S52'=>170,   'S55'=>170,   'S84'=>170,   'S85'=>170,   'S86'=>170,
    //     'SS7'=>170,   'SS8'=>170,   'SS30'=>170,  'SS57'=>170, 'SS254'=>170, 'SS310'=>170, 'SS311'=>170, 'SS402'=>170, 'SS410'=>170, 'SS700'=>170,
    //     'SS710'=>170, 'SS720'=>170, 'SS740'=>170,
    //     // Softshell
    //     '832'=>280, '836'=>260, '837'=>260, '0854'=>200,   '856'=>200, '860'=>260, '861'=>260, '868'=>210, '869'=>210, '0872'=>250, 
    //     '0873'=>250, '875'=>280, '876'=>280, '40836'=>200,
    //     // Veste
    //     '812'=>110,    '820'=>210,    '821'=>210,    '824'=>180, '825'=>180, '892'=>220, '893'=>220, '900'=>190, '1915'=>90, 'G11014'=>170,
    //     'G11031'=>260, 'G21014'=>170, 'G21031'=>260,
    // ); // id=>price

    // Category 3: Forklæder , Poloshirts , Sweatshirts , Tasker
    $c3cats = array( 139, 128, 130, 141 );
    // $c3prices = array( 
    //     // Forklæder
    //     '073'=>70, '074'=>70,
    //     // Poloshirts
    //     '320'=>82,     '321'=>82,   '322'=>94,   '324'=>80,   '326'=>100,  '328'=>88,  '329'=>88,  '330'=>80,  '336'=>100, '374'=>88,
    //     '375'=>88,     '520'=>75,   '521'=>70,   '522'=>77,   '523'=>77,   '0525'=>77, '0527'=>77, '530'=>88,  '531'=>88,  '534'=>97,
    //     '535'=>97,     '544'=>100,  '545'=>100,  '0560'=>100, '0561'=>100, '586'=>88,  '587'=>88,  '2020'=>60, '2022'=>60, 'G11006'=>103,
    //     'G21006'=>103, 'S600'=>103, 'S610'=>103,
    //     // Sweatshirts
    //     '360'=>105,    '362'=>115,    '366'=>170, '367'=>170, '0600'=>88,  '601'=>102,  '603'=>134,  '613'=>129,   '0615'=>89,     '616'=>89,
    //     '622'=>146,    '624'=>146,    '626'=>181, '627'=>181, '628'=>149, '629'=>149,  '630'=>241,  '632'=>135,   '633'=>135,    '0636'=>106,
    //     '0637'=>106,    '0638'=>130,    '0639'=>130, '682'=>101, '683'=>101, '40634'=>75, '40636'=>95, '40638'=>105, 'G11026'=>154, 'G11064'=>190,
    //     'G21026'=>154, 'G21064'=>190,
    //     // Tasker
    //     '1805'=>100, '1810'=>80, '1825'=>110, '1840'=>30, '1850'=>30, '1864'=>139, '1866'=>90, '1868'=>60, '1869'=>120,
    // ); // id=>price


    foreach( $cart_obj->get_cart() as $key=>$value ) {

        $quantity = $value['quantity'];

        $printing_string = $value['thwepof_options']['printing_option']['value'];
        $printing_array = $value['thwepof_options']['printing_option']['options'];
        $printing_option = array_search( $printing_string, $printing_array, true );

        $product_id = $value['product_id'];
        $terms = get_the_terms( $product_id, 'product_cat' );
        $product_cat = array();

        //$flag = 0;
        $defaultPrice = 0;

        foreach ( $terms as $term ) {
            if ( in_array( $term->term_id, $c1cats ) ) {
                $product = wc_get_product( $product_id );
                // $product_sku = $product->get_sku(); 

                // $c1price = $c1prices[$product_sku];

                // if ( !isset($c1prices[$product_sku]) ) $flag = 1;

                $c1price = $product->get_price();

                $c1 = array();
		$c1[0] = array( $c1price, floor($c1price - (8.571 * $c1price/100)), floor($c1price - (17.946 * $c1price/100)), floor($c1price - (26.222 * $c1price/100)), floor($c1price - (34.493 * $c1price/100)), floor($c1price - (40.231 * $c1price/100)), floor($c1price - (44.579 * $c1price/100)));
                $c1[1] = array(round($c1[0][0]*1.29), round($c1[0][1]*1.29), round($c1[0][2]*1.29), round($c1[0][3]*1.29), round($c1[0][4]*1.29), round($c1[0][5]*1.29), round($c1[0][6]*1.29));
                $c1[2] = array(round($c1[0][0]*1.60), round($c1[0][1]*1.60), round($c1[0][2]*1.60), round($c1[0][3]*1.60), round($c1[0][4]*1.60), round($c1[0][5]*1.60), round($c1[0][6]*1.60));
                $c1[3] = array(round($c1[0][0]*1.90), round($c1[0][1]*1.90), round($c1[0][2]*1.90), round($c1[0][3]*1.90), round($c1[0][4]*1.90), round($c1[0][5]*1.90), round($c1[0][6]*1.90));
                $c1[4] = array(round($c1[0][0]*2.20), round($c1[0][1]*2.20), round($c1[0][2]*2.20), round($c1[0][3]*2.20), round($c1[0][4]*2.20), round($c1[0][5]*2.20), round($c1[0][6]*2.20));
                $c1[5] = array(round($c1[0][0]*2.50), round($c1[0][1]*2.50), round($c1[0][2]*2.50), round($c1[0][3]*2.50), round($c1[0][4]*2.50), round($c1[0][5]*2.50), round($c1[0][6]*2.50));
                $c1[6] = array(round($c1[0][0]*2.88), round($c1[0][1]*2.88), round($c1[0][2]*2.88), round($c1[0][3]*2.88), round($c1[0][4]*2.88), round($c1[0][5]*2.88), round($c1[0][6]*2.88));
                $c1[0] = array(round($c1[0][0]), round($c1[0][1]), round($c1[0][2]), round($c1[0][3]), round($c1[0][4]), round($c1[0][5]), round($c1[0][6]) );
                $current_cat = $c1;
            }

            if ( in_array( $term->term_id, $c2cats ) ) {
                $product = wc_get_product( $product_id );
                // $product_sku = $product->get_sku(); 

                // $c2price = $c2prices[$product_sku];

                // if ( !isset($c2prices[$product_sku]) ) $flag = 1;

                $c2price = $product->get_price();

                $c2 = array();
		$c2[0] = array( $c2price, floor($c2price - (6.897 * $c2price/100)), floor($c2price - (10.601 * $c2price/100)), floor($c2price - (14.447 * $c2price/100)), floor($c2price - (18.447 * $c2price/100)), floor($c2price - (21.780 * $c2price/100)), floor($c2price - (25.228 * $c2price/100)) );
                $c2[1] = array(round($c2[0][0]*1.09), round($c2[0][1]*1.09), round($c2[0][2]*1.09), round($c2[0][3]*1.09), round($c2[0][4]*1.09), round($c2[0][5]*1.09),round($c2[0][6]*1.09));
                $c2[2] = array(round($c2[0][0]*1.13), round($c2[0][1]*1.13), round($c2[0][2]*1.13), round($c2[0][3]*1.13), round($c2[0][4]*1.13), round($c2[0][5]*1.13),round($c2[0][6]*1.13));
                $c2[3] = array(round($c2[0][0]*1.17), round($c2[0][1]*1.17), round($c2[0][2]*1.17), round($c2[0][3]*1.17), round($c2[0][4]*1.17), round($c2[0][5]*1.17),round($c2[0][6]*1.17));
                $c2[4] = array(round($c2[0][0]*1.23), round($c2[0][1]*1.23), round($c2[0][2]*1.23), round($c2[0][3]*1.23), round($c2[0][4]*1.23), round($c2[0][5]*1.23),round($c2[0][6]*1.23));
                $c2[5] = array(round($c2[0][0]*1.27), round($c2[0][1]*1.27), round($c2[0][2]*1.27), round($c2[0][3]*1.27), round($c2[0][4]*1.27), round($c2[0][5]*1.27),round($c2[0][6]*1.27));
                $c2[6] = array(round($c2[0][0]*1.32), round($c2[0][1]*1.32), round($c2[0][2]*1.32), round($c2[0][3]*1.32), round($c2[0][4]*1.32), round($c2[0][5]*1.32),round($c2[0][6]*1.32));
                $c2[0] = array(round($c2[0][0]), round($c2[0][1]), round($c2[0][2]), round($c2[0][3]), round($c2[0][4]), round($c2[0][5]), round($c2[0][6]) );
                $current_cat = $c2;
            }

            if ( in_array( $term->term_id, $c3cats ) ) {
                $product = wc_get_product( $product_id );
                // $product_sku = $product->get_sku(); 

                // $c3price = $c3prices[$product_sku];

                // if ( !isset($c3prices[$product_sku]) ) $flag = 1;

                $c3price = $product->get_price();
				if ($_GET['dev']==1) {
					print_R($c3price);
				}
                $c3 = array();
		$c3[0] = array( $c3price, floor($c3price - (3.571 * $c3price/100)), floor($c3price - (3.704 * $c3price/100)), floor($c3price - (3.846 * $c3price/100)), floor($c3price - (4.800 * $c3price/100)), floor($c3price - (4.202 * $c3price/100)), floor($c3price - (3.509 * $c3price/100)) );
                $c3[1] = array(round($c3[0][0]*1.09), round($c3[0][1]*1.09), round($c3[0][2]*1.09), round($c3[0][3]*1.09), round($c3[0][4]*1.09), round($c3[0][5]*1.09),round($c3[0][6]*1.09));
                $c3[2] = array(round($c3[0][0]*1.13), round($c3[0][1]*1.13), round($c3[0][2]*1.13), round($c3[0][3]*1.13), round($c3[0][4]*1.13), round($c3[0][5]*1.13),round($c3[0][6]*1.13));
                $c3[3] = array(round($c3[0][0]*1.17), round($c3[0][1]*1.17), round($c3[0][2]*1.17), round($c3[0][3]*1.17), round($c3[0][4]*1.17), round($c3[0][5]*1.17),round($c3[0][6]*1.17));
                $c3[4] = array(round($c3[0][0]*1.23), round($c3[0][1]*1.23), round($c3[0][2]*1.23), round($c3[0][3]*1.23), round($c3[0][4]*1.23), round($c3[0][5]*1.23),round($c3[0][6]*1.23));
                $c3[5] = array(round($c3[0][0]*1.27), round($c3[0][1]*1.27), round($c3[0][2]*1.27), round($c3[0][3]*1.27), round($c3[0][4]*1.27), round($c3[0][5]*1.27),round($c3[0][6]*1.27));
                $c3[6] = array(round($c3[0][0]*1.32), round($c3[0][1]*1.32), round($c3[0][2]*1.32), round($c3[0][3]*1.32), round($c3[0][4]*1.32), round($c3[0][5]*1.32),round($c3[0][6]*1.32));
                $c3[0] = array( round($c3[0][0]), round($c3[0][1]), round($c3[0][2]), round($c3[0][3]), round($c3[0][4]), round($c3[0][5]), round($c3[0][6]) );
                $current_cat = $c3;
            }
			if (!in_array( $term->term_id, $c1cats) && !in_array( $term->term_id, $c2cats) && !in_array( $term->term_id, $c3cats)) {
				$product = wc_get_product( $product_id );
				$defaultPrice = $product->get_price();
			}
        }

        // if ( $flag == 1 ) {
        //     continue;
        // }

	if ($quantity < 10) {
            //continue;
            $price = $current_cat[$printing_option][0];
        } else if ($quantity >= 10 && $quantity < 25) {
            $price = $current_cat[$printing_option][0];
        } else if ($quantity >= 25 && $quantity < 50) {
            $price = $current_cat[$printing_option][1];
        } else if ($quantity >= 50 && $quantity < 100) {
            $price = $current_cat[$printing_option][2];
        } else if ($quantity >= 100 && $quantity < 250) {
            $price = $current_cat[$printing_option][3];
        } else if ($quantity >= 250 && $quantity < 500) {
            $price = $current_cat[$printing_option][4];
        } else if ($quantity >= 500 && $quantity < 1000) {
            $price = $current_cat[$printing_option][5];
        } else if ($quantity > 999) {
            $price = $current_cat[$printing_option][6];
        }	
	
	if ( $defaultPrice !== 0 ) {
		$value['data']->set_price($defaultPrice);
	} else {
		$value['data']->set_price( ( $price ) );
	}

    }

    // $user = wp_get_current_user();
    // if ($user->ID != 0) {
    //     // if ($user->roles[0] == 'partner') {
    //         if ($quantity >= 1 && $quantity <= 5) {
    //             $price = 45;
    //         } else if ($quantity >= 6 && $quantity <= 14) {
    //             $price = 35;
    //         } else if ($quantity >= 15 && $quantity <= 19) {
    //             $price = 30;
    //         } else if ($quantity >= 20) {
    //             $price = 25;
    //         }
    //     // }
    // }

// }

}


add_filter( 'woocommerce_email_order_items_args', 'custom_email_order_items_args', 10, 1 );
function custom_email_order_items_args( $args ) {
    $args['show_image'] = true;
    // $args['image_size'] = array( 150, 150 );

    return $args;
}


add_filter( 'woocommerce_email_styles', 'woocommerce_email_styles_func', 9999, 2 );
function woocommerce_email_styles_func( $css, $email ) {
   $css .= '
      .woocommerce-Price-currencySymbol { float: left; }
   ';
   return $css;
}



// ID 0505: missing prices 505.xls (T-Shirt)
// ID 0570: missing prices 570.xls (T-Shirt)
// ID 0571: missing prices 571.xls (T-Shirt)
// ID 1800: missing prices 1800.xls (Tasker)
// ID 1801: missing prices 1801.xls (Tasker)
// ID 1802: missing prices 1802.xls (Tasker)
// ID 1815: missing prices 1815.xls (Tasker)
// ID 1845: missing prices 1845.xls (Muleposer)
// ID 204: missing prices 204.xls (Skjorter)
// ID 300: missing prices 300.xls (T-Shirt)
// ID 40600: missing prices 40600.xls (Børnetøj)
// ID 40610: missing prices 40610.xls (Børnetøj)
// ID 41902: missing prices 41902.xls (Børnetøj)
// ID 533: missing prices 533.xls (Poloshirts)
// ID 604: missing prices 604.xls (Sweatshirt)
// ID 6818: missing prices 6818.xls (Muleposer)
// ID 7302: missing prices 7302.xls (Muleposer)
// ID 811: missing prices 811.xls (Fleece, Veste)
// ID 852: missing prices 852.xls (Fleece, Sweatshirt, Veste)
// ID 853: missing prices 853.xls (Fleece, Jakker, Sweatshirt)
// ID 864: missing prices 864.xls (Fleece, Sweatshirt, Veste)
// ID 865: missing prices 865.xls (Fleece, Sweatshirt, Veste)
// ID G11002: missing prices G11002.xls (T-Shirt)
// ID G11006: missing prices G11006.xls (Poloshirt)
// ID G21002: missing prices G21002.xls (Sportstøj)
// ID G21022: missing prices G21022.xls (Sportstøj)
// ID MB006: missing prices MB006.xls (Kasketter)
// ID MB070: missing prices MB070.xls (Kasketter)
// ID MB071: missing prices MB071.xls (Kasketter)
// ID MB095: missing prices MB095.xls (Kasketter)
// ID MB6117: missing prices MB6117.xls (Kasketter)
// ID S610: missing prices S610.xls (Poloshirt)
// ID S721: missing prices S721.xls (Skjorter)
// ID SS343: missing prices SS343.xls (Skjorter)

// ID 315: missing product in WooCommerce (Which category?)
// ID 366: missing product in WooCommerce (Which category?)

// ID 1840: duplicate price file 1840.xls in Muleposer and Tasker
// ID 40500: duplicate price file 40500.xls in Børnetøj and T-Shirts
// ID 40510: duplicate price file 40510.xls in Børnetøj and T-Shirts
// ID 40552: duplicate price file 40552.xls in Børnetøj and T-Shirts
// ID 40636: duplicate price file 40636.xls in Børnetøj and Sweatshirts
// ID 40638: duplicate price file 40638.xls in Børnetøj and Sweatshirts
// ID 42030: duplicate price file 42030.xls in Børnetøj and T-Shirts

// ID 587: veste + poloshirts = multiple category

// ID 0872-1 same as 0872? What to do with it?
// ID 514--1 same as 514--2? What to do with it?

// https://texprint.dk/produkt/klassisk-t-shirt/
// Med 1 farvet tryk
//  9: 60,00 DKK
// 10: 77,00 DKK

*/


