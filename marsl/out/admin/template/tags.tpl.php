<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<br /><br />
<fieldset class="alphabetsearch">
	<form method="get" action="index.php?var=tags">
		<input type="text" name="search" />
		<input type="hidden" name="var" value="tags" />
		<button> Tag suchen </button>
	</form>
</fieldset>
<div class="alphabetmenu">
	<ul>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=%">Alle</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=0">0</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=1">1</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=2">2</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=3">3</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=4">4</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=5">5</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=6">6</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=7">7</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=8">8</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=9">9</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=a">A</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=b">B</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=c">C</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=d">D</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=e">E</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=f">F</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=g">G</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=h">H</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=i">I</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=j">J</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=k">K</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=l">L</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=m">M</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=n">N</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=o">O</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=p">P</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=q">Q</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=r">R</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=s">S</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=t">T</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=u">U</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=v">V</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=w">W</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=x">X</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=y">Y</a>
		</li>
		<li>
			<a class="hide" href="index.php?var=tags&amp;search=z">Z</a>
		</li>
	</ul>
	<br />
</div>
<?php if ($newEntry&&$entrySuccessful): ?>
<br /><div class="success">Der Eintrag wurde erfolgreich hinzugef&uuml;gt.</div>
<?php endif; ?>
<?php if ($newEntry&&!$entrySuccessful): ?>
<br /><div class="caution">Der Eintrag konnte nicht hinzugef&uuml;gt werden! Es existiert schon ein Eintrag mit dem Namen.</div>
<?php endif; ?>
<?php if ($deletionSuccessful): ?>
<br /><div class="success">Der Eintrag wurde erfolgreich gel&ouml;scht.</div>
<?php endif; ?>
<div class="tags">
	<table class="cbelist">
		<?php foreach ($tags as $tag): ?>
		<tr>
			<td>
				<a href="index.php?var=tags&amp;action=edit&amp;tagid=<?php echo $tag['id']; ?>">
					<?php echo $tag['tag']; ?>
				</a>
			</td>
			<td>
				<a href="index.php?var=tags&amp;search=<?php echo $search; ?>&amp;action2=delete&amp;tagid=<?php echo $tag['id']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du den Eintrag wirklich l&ouml;schen?')">
					L&ouml;schen
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<br />
<fieldset class="alphabetsearch">
	<h3>Neuer Tag:</h3>
	<form method="post" action="index.php?var=tags&amp;search=%">
		<input type="text" name="entry" />
		<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
		<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
		<button name="action" value="newTag"> Tag eintragen </button>
	</form>
</fieldset>