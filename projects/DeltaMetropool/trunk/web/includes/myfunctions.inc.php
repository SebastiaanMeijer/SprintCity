<?php
	function printPager($pager, $view)
	{
		// do not display if there is only one page
		if ($pager->numPages <= 1)
			return;
?>
	<ul class="pager">
<?php
		printPagerItem("vorige", $pager->prevPage(), $view, $pager->hasPrevPage());
		for ($i = 1; ($i <= $pager->numPages); $i++)
		{
			printPagerItem($i, $i, $view, true, $i == $pager->page);
		}
		printPagerItem("volgende", $pager->nextPage(), $view, $pager->hasNextPage());
?>
	</ul>
<?php
	}
	
	function printPagerItem($text, $page, $view, $enabled = true, $current = false)
	{
		echo "\t\t";
		if ($current)
			echo '<li class="selected">';
		else if (!$enabled)
			echo '<li class="disabled">';
		else
			echo '<li>';
		if ($enabled && !$current)
			echo '<a href="admin.php?view=' . $view . '&page=' . $page . '">';
		echo $text;
		if ($enabled && !$current)
			echo '</a>';
		echo '</li>' . "\n";
	}
?>
