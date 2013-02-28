<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<script type="text/javascript">
$(document).ready(function(){  
    $(document).ready(function(){  
        $("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);  
        $("#featured").hover(  
        function() {  
        $("#featured").tabs("rotate",0,true);  
        },  
        function() {  
        $("#featured").tabs("rotate",5000,true);  
        }  
        ); 
        });
	var tabcontainers=$('#container > div');  
	//hide every content div  
	tabcontainers.hide();  
	  
	$('#tabs > li a').click(function(){  
	    //On click on the tab navigation link  
	    tabcontainers.hide();  
	    //show content block corresponding to link clicked  
	    tabcontainers.filter(this.hash).fadeIn();  
	    $('#tabs > li a').removeClass('selected');  
	    $(this).addClass('selected');  
	    return false;  
	}).filter(':first').click();//click event for first link on pageload  

});  
</script>
<div id="featured" >  
	<ul class="ui-tabs-nav">
		<?php $i = 0; 
		foreach($news as $article): $i++; ?>
		<li class="ui-tabs-nav-item" id="nav-fragment-<?php echo $i; ?>">
			<a href="#fragment-<?php echo $i; ?>">
				<span><b><?php echo $article['headline']; ?>: <?php echo $article['title']; ?></b><br /><?php echo $article['date']; ?></span>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php $i= 0;
	foreach ($news as $article): $i++; ?>
	<div id="fragment-<?php echo $i; ?>" class="ui-tabs-panel" onmouseover="this.style.cursor = 'pointer'" onclick="window.location = 'index.php?id=<?php echo $article['location']; ?>&amp;show=<?php echo $article['news']; ?>&amp;action=read'">
		<img src="news/<?php echo $article['picture']; ?>" />
		<div class="info">
			<h3>
				<?php echo $article['headline']; ?>
			</h3>
			<h2>
				<?php echo $article['title']; ?>
			</h2>
			<p>
				<?php echo $article['teaser']; ?>
				<?php echo $article['photograph']; ?>
			</p>
		</div>
	</div>
	<?php endforeach; ?>  
</div>  