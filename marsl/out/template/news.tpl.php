<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<div class="news">
	<table>
		<tr>
			<td class="newsinformation">
				<h3>Taglist:</h3>
				<?php foreach($moduleTags as $moduleTag):
				$tags = $moduleTag['tags'];
				?>
				<b><?php echo $moduleTag['name']; ?></b><br />
				<?php foreach($tags as $tag): ?>
				<a href="index.php?tag=<?php echo $tag['id']; ?>&amp;scope=<?php echo $moduleTag['type']; ?>"><?php echo htmlentities($tag['tag'], null, "ISO-8859-1"); ?></a><br />
				<?php endforeach; ?>
				<br />
				<?php endforeach; ?>
				<br />
				<div class="newsbottom">
					<div class="fb-like" data-href="<?php echo $url; ?>" data-colorscheme="light" data-layout="box_count" data-action="like" data-show-faces="true" data-send="false""></div>
					<a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical" data-url="<?php echo $url; ?>" data-via="music2web" data-lang="de"></a>
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
					<script type="text/javascript" src="http://apis.google.com/js/plusone.js">{lang: 'de', parsetags: 'explicit'}</script><g:plusone href="<?php echo $url; ?>" size="tall"></g:plusone><script type="text/javascript">gapi.plusone.go();</script>
					<br /><img src="includes/graphics/square.gif" /> Datum: <?php echo $date; ?>
				</div>
			</td>
			<td class="newscontent">
				<h3 class="headline"><?php echo $headline; ?></h3>
				<h2><?php echo $title; ?></h2>
				<?php if (($picture2=="empty")&&($picture1!="empty")): ?>
				<span class="teaserpicture">
					<img src="news/<?php echo $picture1; ?>" />
					<?php echo $photograph1; ?>
				</span>
				<?php endif; ?>
				<b><?php echo $city; ?> (<?php echo $authorName; ?>)&nbsp;<img src="includes/graphics/square.gif" />&nbsp;&nbsp;&nbsp;<?php echo $teaser; ?></b><br />
				<?php if ($picture2!="empty"): ?>
				<br />
				<span class="center">
					<div class="textpicture">
						<img src="news/<?php echo $picture2; ?>" />
						<div class="subtitle"><b><?php echo $subtitle2; ?><?php echo $photograph2; ?></b></div>
					</div>
				</span>
				<?php endif; ?>
				<br /><?php echo $text; ?><br /><br />
				<script>(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) {return;}
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/de_DE/all.js#xfbml=1";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>
				
				<div class="fb-comments" data-href="<?php echo $url; ?>" data-num-posts="10" data-width="640"></div>
			</td>
		</tr>
	</table>
	<hr class="newsseparator" />
</div>