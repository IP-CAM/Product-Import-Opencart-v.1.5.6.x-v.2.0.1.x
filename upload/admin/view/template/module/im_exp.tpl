<?php echo $header; ?>
<div id="content" class="im_exp_block">
	<div class="breadcrumb">
	    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
	    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
	    <?php } ?>
	</div>
	<?php if ($error_warning) { ?>
	<div class="warning"><?php echo $error_warning; ?></div>
	<?php } ?>
	<?php if ($success) { ?>
	<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	 <div class="box">
	    <div class="heading">
	      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
	    </div>
	 </div>
	 <form action="<?php echo $import_url; ?>" class="im_exp" method="POST" enctype="multipart/form-data">
	 	<input type="file" name="csv" id="file" />
	 	<!--<input type="hidden" value="<?php echo $session; ?>" name="token">
	 	<input type="hidden" value="module/im_exp/import" name="route">-->
	 	<a href="#" class="button" id="import">Импорт</a>
	 	<!-- <a href="<?php echo $export_url; ?>" class="button">Экспорт</a> -->
	 	<div class="select_tpl">
	 		Выбрать шаблон:
	 		<select name="ready_tpl" id="">
	 			<option selected value="none">Не выбранно</option>
	 			<?php foreach ($saveorderlist as $key => $value) { ?>	 
  					<option value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
	 			<? } ?>
	 		</select>
	 		<a href="<?php echo $delorder; ?>" class="button" id="del_tpl">Удалить шаблон</a>
	 	</div>
	 	<div class="clear"></div>
		<!-- <div class="list left">
			<p>Перетащите обезательные поля</p>
			<ul id="sortable1" class="droptrue">
			  <!-- <li><input type="hidden" name=""></li> -->
			<!--</ul>
		</div> -->
		<div class="list center">
			<p>Перетащите не обезательные поля</p>
			<ul id="sortable2" class="droptrue">
			  <li class="ui-state-default"><input type="hidden" name="product_model">Модель</li>
			  <li class="ui-state-default"><input type="hidden" name="product_price">Цена</li>
			  <li class="ui-state-default"><input type="hidden" name="product_description_name">Название</li>
			  <li class="ui-state-default"><input type="hidden" name="product_description_description">Описание</li>
			  <li class="ui-state-default"><input type="hidden" name="product_to_category_category_id">Основная категория</li>
			  <li class="ui-state-default"><input type="hidden" name="product.status">Показывать</li>
			  <!-- -- -->
			  <li class="ui-state-default"><input type="hidden" name="product_quantity">Количество</li>
			  <li class="ui-state-default"><input type="hidden" name="product_stock_status_id">Статус при отсуствии</li>
			  <li class="ui-state-default"><input type="hidden" name="product_image">Главное изображение</li>
			  <li class="ui-state-default"><input type="hidden" name="product_manufacturer_id">Производитель</li>
			  <!-- <li class="ui-state-default"><input type="hidden" name="product_status">Включен</li> -->
			  <li class="ui-state-default"><input type="hidden" name="product_attribute">Атрибуты (найминование_значения,найминование_значения)</li>
			  <li class="ui-state-default"><input type="hidden" name="product_description_meta_description">Мета описание</li>
			  <li class="ui-state-default"><input type="hidden" name="product_description_meta_keyword">Мета кейвордс</li>
			  <li class="ui-state-default"><input type="hidden" name="product_description_seo_title">SEO title</li>
			  <li class="ui-state-default"><input type="hidden" name="product_description_seo_h1">SEO H1</li>
			  <li class="ui-state-default"><input type="hidden" name="product_description_tag">Теги</li>
			  <li class="ui-state-default"><input type="hidden" name="product_image_id_image">Дополнительные изображения</li>
			  <li class="ui-state-default"><input type="hidden" name="product_special_price">Цена со скидкой</li>
			  <li class="ui-state-default"><input type="hidden" name="product_to_category_category_id_else">Дополнительные категории</li>
			</ul>
		</div>
		<div class="list right">
			<p>Выбраный порядок</p>
			<ul id="sortable3" class="dropfalse">
				<li class="ui-state-default"><input type="hidden" name="product_sku">Код товара</li>
			</ul>
			<a href="<?php echo $saveorder ?>" id="saveorder" class="button">Сохранить шаблон прайс-листа</a>
		</div>
	</form>
</div>
<?php echo $footer; ?>