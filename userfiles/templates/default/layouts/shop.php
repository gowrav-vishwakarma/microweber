<?php

/*

type: layout
content_type: dynamic
name: Online Shop
is_shop: y
description: shop layout
position: 4
*/


?>
<?php include THIS_TEMPLATE_DIR. "header.php"; ?>
<section id="content">
	<div class="container">
		<div class="row" id="shop-products-conteiner">
			<div class="span12 edit"  field="content" rel="page">
				<h2><?php print $page['title'] ?></h2>

				<p class="p0 element">This text is set by default and is suitable for edit in real time. By default the drag and drop core feature will allow you to position it anywhere on the site. Get creative & Make Web.</p>
			</div>
		</div>
		<div class="row" id="shop-products-conteiner"> 
			<!-------------- Blog post -------------->
			<div class="span8 edit"  field="content2" rel="page">
				<module type="shop/products" template="columns" limit="18" description-length="70" hide-paging="n"   />
			</div>
			<!------------ Sidebar -------------->
			<div class="span4">
				<?php
		if(is_file(THIS_TEMPLATE_DIR. 'layouts' . DS."shop_sidebar.php")){
 include THIS_TEMPLATE_DIR. 'layouts' . DS."shop_sidebar.php"; 
 
} else if(is_file(DEFAULT_TEMPLATE_DIR.  'layouts' . DS."shop_sidebar.php")){
	include DEFAULT_TEMPLATE_DIR. 'layouts' . DS."shop_sidebar.php"; 
}
		  ?>
			</div>
		</div>
	</div>
</section>
<?php include THIS_TEMPLATE_DIR. "footer.php"; ?>
