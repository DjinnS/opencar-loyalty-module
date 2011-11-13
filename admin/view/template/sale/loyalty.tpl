<!-- /*
* Author  : DjinnS - djinns@chninkel.net
* 
* License : GPL v2 (http://www.gnu.org/licenses/gpl-2.0.html)
*/
-->

<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>                                                                                                                                                               <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($text_error) { ?>
  <div class="warning"><?php echo $text_error; ?></div>
  <?php } ?>
  <?php if ($text_success) { ?>
  <div class="success"><?php echo $text_success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/total.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $loyalty_rate; ?></td>
            <td><input name="loyalty_rate" size="1" value="<? echo $loyalty_config_rate; ?>" /> <?php echo $loyalty_currency; ?> = 1 point</td>
			<td><i><?php echo $loyalty_rate_explain; ?></i></td>
          </tr>
          <tr>
            <td><?php echo $loyalty_threshold; ?></td>
            <td><input name="loyalty_threshold" size="1" value="<? echo $loyalty_config_threshold; ?>" /></td>
			<td><i><?php echo $loyalty_threshold_explain; ?></i></td>
          </tr>
          <tr>
            <td><?php echo $loyalty_gain; ?></td>
            <td><input name="loyalty_gain" size="1" value="<? echo $loyalty_config_gain; ?>" /> <?php echo $loyalty_currency; ?></td>
			<td><i><?php echo $loyalty_gain_explain; ?></i></td>
          </tr>
		  <tr>
			<td><?php echo $loyalty_voucher; ?></td>
			<td>
				<?php if ($voucher_themes) { ?>
				<select name="loyalty_voucherid" id="loyalty_voucherid">
					<?php foreach ($voucher_themes as $voucher_theme) { ?>
					<option <?php if($voucher_theme['selected'] == 1) echo "selected"; ?> value="<?php echo $voucher_theme['voucher_theme_id']; ?>"><?php echo $voucher_theme['name']; ?></option>
					<?php } ?>
				</select>
				<?php } ?>
			</td>
			<td><i><?php echo $loyalty_voucher_explain; ?></i></td>
		  </tr>
		  <tr>
			<td><?php echo $loyalty_order_status; ?> id: <?php echo $loyalty_config_order_status; ?></td>
             <td>
				<select name="loyalty_order_statusid">
                  <?php foreach ($order_statuses as $order_status) { ?>
	              <option <?php if($order_status['order_status_id'] == $loyalty_config_order_status) echo "selected"; ?> value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                  <?php } ?>
                </select>
			</td>
			<td><i><?php echo $loyalty_order_status_explain; ?></i></td> 
		  </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>
