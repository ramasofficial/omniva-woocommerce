<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wrap">
<h1><?php _e('Omniva manifest','omnivalt'); ?></h1>
<?php
global $wpdb;

// The SQL query
$items = $wpdb->get_results( "
    SELECT wpp.*
    FROM {$wpdb->prefix}posts as wpp
    LEFT JOIN {$wpdb->prefix}woocommerce_order_items as woi ON woi.order_id = wpp.id
    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woim ON woim.order_item_id = woi.order_item_id AND woim.meta_key = 'method_id'
    WHERE woim.meta_value IN ('omnivalt_pt','omnivalt_c','omnivalt')
" );
?>
<?php if(count($items)): ?>
<?php
//group items by shipping date DESC
$grouped_items = array();
foreach ($items as $item){
  $generation_date = get_post_meta($item->ID,'_manifest_generation_date',true);
  if (!$generation_date)
    $date = "new";
  else
    $date = date('Y-m-d H:i',strtotime($generation_date));
  if (!isset($grouped_items[$date])) $grouped_items[$date] = array();
  $grouped_items[$date][] = $item;
}
krsort($grouped_items);
$items_ignore = array();
?>
<?php
$p_limit = 4;
$total_pages = ceil(count($grouped_items)/$p_limit);
$current_page = 1;

if (isset($_GET['paged']))
  $current_page = $_GET['paged'];
if ($current_page > $total_pages) $current_page = $total_pages;
$counter = 0;
?>
<?php
$page_links = paginate_links( array(
    'base' => add_query_arg( 'paged', '%#%' ),
    'format' => '?paged=%#%',
    'prev_text' => __( '&laquo;', 'text-domain' ),
    'next_text' => __( '&raquo;', 'text-domain' ),
    'total' => $total_pages,
    'current' => $current_page,
    'type' => 'plain'
) );

if ( $page_links ) {
    echo '<div class="tablenav" style = "float:left;"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div><div class = "clear"></div>';
}
?>
<?php foreach ($grouped_items as $date=>$orders): ?>
<?php
  $counter++;
  if ($current_page*$p_limit-$p_limit >= $counter) continue;
  if (($current_page)*$p_limit < $counter) break;
?>
<br/>
<h3><?php echo ($date=="new"?__('New orders','omnivalt'):$date); ?></h3>
<div class = "">
<table class="wp-list-table widefat fixed striped posts">
    <thead>
    <tr >
      <td class = "manage-column column-cb check-column"><input type = "checkbox"  class = "check-all"/></td>
      <th class = "manage-column"><?php echo __('Order #','omnivalt');?></th>
      <th class = "manage-column"><?php echo __('Manifest generation date','omnivalt'); ?></th>
	  <th class = "manage-column">Siuntos numeris</th>
		<th class = "manage-column">Išsiųsta klientui</th>
      <th>Lipdukai</th>
    </tr>
    </thead>
    <tbody>
        <?php $_odd = ''; ?>
        <?php foreach ($orders as $order): ?>
        <tr class = "data-row">
            <?php
            $order_items = array();
            ?>
            <th class = "check-column"><input type = "checkbox" name = "items[]" class = "manifest-item" value = "<?php echo $order->ID; ?>"/></th>
            <td><div class = "data-grid-cell-content"><?php echo $order->ID; ?></div></td>
            <td><div class = "data-grid-cell-content"><?php echo get_post_meta($order->ID,'_manifest_generation_date',true); ?></div></td>
			<td>
				<?php
					$track_code = get_post_meta($order->ID,'_omnivalt_barcode',true);
					if(!empty($track_code))
					{
						echo '<input value="'.get_post_meta($order->ID,'_omnivalt_barcode',true).'" name="barcode" type="text" readonly/><br/><button style="margin-top: 6px;" class="button send-customer-barcode" data-order-id="'.$order->ID.'" data-barcode="'.get_post_meta($order->ID,'_omnivalt_barcode',true).'">'.__('Send to client','omnivalt').'</button> <a style="margin-top: 6px;" class="button" href="https://www.omniva.lt/verslo/siuntos_sekimas?barcode='.$track_code.'" target="_blank">Sekti</a>';
					}
				?>
			</td>
			<td>
				<?php
						$send = get_post_meta($order->ID,'_omnivalt_emails',true);

						if(!empty($send))
						{
								echo '<span style="color: green;">Taip</span>';
						} else {
								echo '<span style="color: red;">Ne</span>';
						}
				?>
			</td>
            <td>
            <form action = "admin-post.php" method = "GET">
              <input type = "hidden" name = "action" value = "omnivalt_labels"/>
              <input type="hidden" name="post[]" value = "<?php echo $order->ID; ?>" />
              <?php wp_nonce_field( 'omnivalt_labels', 'omnivalt_labels_nonce' ); ?>
               <button title="<?php echo __('Print label','omnivalt');?>" type="submit" class="button action">
                <?php echo __('Print labels','omnivalt');?>
              </button>
            </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endforeach; ?>
<div>
<br/>
  <div class="f-left">
    <form id = "manifest-print-form" action = "admin-post.php" method = "GET">
      <input type = "hidden" name = "action" value = "omnivalt_manifest"/>
      <?php wp_nonce_field( 'omnivalt_manifest', 'omnivalt_manifest_nonce' ); ?>
    </form>
    <form id = "labels-print-form" action = "admin-post.php" method = "GET">
      <input type = "hidden" name = "action" value = "omnivalt_labels"/>
      <?php wp_nonce_field( 'omnivalt_labels', 'omnivalt_labels_nonce' ); ?>
    </form>
    <button id="submit_manifest_items" title="<?php echo __('Generate manifest','omnivalt');?>" type="button" class="button action">
      <?php echo __('Generate manifest','omnivalt');?>
    </button>
    <button id="submit_manifest_labels" title="<?php echo __('Print labels','omnivalt');?>" type="button" class="button action">
      <?php echo __('Print labels','omnivalt');?>
    </button>
		<button id="submit_manifest_emails" title="<?php echo __('Send to clients','omnivalt');?>" type="button" class="button action">
      <?php echo __('Send to clients','omnivalt');?>
    </button>
  </div>
  <div class="f-clear"></div>
</div>
<script>
jQuery('document').ready(function($){
  $('#submit_manifest_items').on('click',function(){
    var ids = "";
    $('#manifest-print-form .post_id').remove();
    $('.manifest-item:checked').each(function() {
      ids += $(this).val()+";";
      var id = $(this).val();
       $('#manifest-print-form').append('<input type="hidden" class = "post_id" name="post[]" value = "'+id+'" />');
    });
    $('#item_ids').val(ids);
    if (ids == ""){
      alert('<?php echo __('Select orders','omnivalt'); ?>');
    } else {
      $('#manifest-print-form').submit();
    }
    //console.log($('#item_ids').val());

  });

  $('#submit_manifest_labels').on('click',function(){
    var ids = "";
    $('#labels-print-form .post_id').remove();
    $('.manifest-item:checked').each(function() {
      ids += $(this).val()+";";
      var id = $(this).val();
       $('#labels-print-form').append('<input type="hidden" class = "post_id" name="post[]" value = "'+id+'" />');
    });
    if (ids == ""){
      alert('<?php echo __('Select orders','omnivalt'); ?>');
    } else {
      $('#labels-print-form').submit();
    }
    //console.log($('#item_ids').val());

  });

	$('#submit_manifest_emails').on('click',function(){
		var ids = "";
    $('.manifest-item:checked').each(function() {
			ids += $(this).val()+";";
      var id = $(this).val();

			$.ajax({
			  method: "POST",
			  url: ajaxurl,
			  data: { action: "woocommerce_add_order_note", post_id: id, note: "OMNIVA: "+$('button[data-order-id="'+id+'"]').attr('data-barcode'), note_type: "customer", security: "<?php echo wp_create_nonce('add-order-note'); ?>", }
		  }).done(function(response) {
			  $('button[data-order-id="'+id+'"]').hide();
				console.log('Siuntos kodas užsakymui: '+id+': '+$('button[data-order-id="'+id+'"]').attr('data-barcode')+' sėkmingai išsiųstas!');

				$.ajax({
				  method: "GET",
				  url: "admin-post.php",
				  data: { action: "omnivalt_emails", post_id: id, omnivalt_emails_nonce: "<?php echo wp_create_nonce('omnivalt_emails_nonce'); ?>", }
			  }).done(function(response) {
						location.reload();
			  });
			});

    });

		if (ids == ""){
      alert('<?php echo __('Select orders','omnivalt'); ?>');
    }
  });

    $('.check-all').on('click',function(){
      var checked = $(this).prop('checked');
      $(this).parents('table').find('.manifest-item').each(function() {
        $(this).prop('checked', checked);
      });
    });

	$('.send-customer-barcode').on('click',function(){
      var siuntos_kodas = $(this).attr('data-barcode');
	  var order_id = $(this).attr('data-order-id');

	  $.ajax({
		  method: "POST",
		  url: ajaxurl,
		  data: { action: "woocommerce_add_order_note", post_id: order_id, note: "OMNIVA: "+siuntos_kodas, note_type: "customer", security: "<?php echo wp_create_nonce('add-order-note'); ?>", }
	  }).done(function(response) {
		  $('button[data-order-id="'+order_id+'"]').hide();

			$.ajax({
			  method: "GET",
			  url: "admin-post.php",
			  data: { action: "omnivalt_emails", post_id: order_id, omnivalt_emails_nonce: "<?php echo wp_create_nonce('omnivalt_emails_nonce'); ?>", }
		  }).done(function(response) {
					location.reload();
		  });

		  alert("Sėkmingai išsiųsta klientui!");
	  });

    });
});
</script>
<?php else: ?>
    <p><?php echo __('No assign shipments found','omnivalt'); ?></p>
<?php endif ?>

</div>
