<?php
if ( ! defined ( 'gamepl_er6tybuniomop' ) ) {
	header ( 'Location:http://gamepl.ru' );
	exit;
}
$true = true;

class bbcode
{
	public static function html ( $bbtext )
	{
		//$bbtext= str_replace('http://','https://',$bbtext);
		return $bbtext;
	}

	public static function forum ( $bbtext )
	{
		//$bbtext= str_replace('http://','https://',$bbtext);
		$bbtags = array(
			'[p]' => '<p>','[/p]' => '</p>',
			'[left]' => '<p style=\'text-align:left;\'>','[/left]' => '</p>',
			'[right]' => '<p style=\'text-align:right;\'>','[/right]' => '</p>',
			'[center]' => '<p style=\'text-align:center;\'>','[/center]' => '</p>',
			'[justify]' => '<p style=\'text-align:justify;\'>','[/justify]' => '</p>',
			'[ul]' => '<ul>','[/ul]' => '</ul>',
			'[ol]' => '<ol>','[/ol]' => '</ol>',
			'[li]' => '<li>','[/li]' => '</li>',
			'[*]' => '<li>','[/*]' => '</li>',
			'[b]' => '<b>','[/b]' => '</b>',
			'[u]' => '<u>','[/u]' => '</u>',
			'[i]' => '<i>','[/i]' => '</i>',
			'[s]' => '<strike>','[/s]' => '</strike>',
			'[quote]' => '<blockquote>','[/quote]' => '</blockquote>',
			'[code]' => '<code>','[/code]' => '</code>',
			'[table]' => '<table class="table">','[/table]' => '</table>',
			'[tr]' => '<tr>','[/tr]' => '</tr>',
			'[td]' => '<td>','[/td]' => '</td>'
		);

		$bbtext = str_ireplace(array_keys($bbtags), array_values($bbtags), $bbtext);

		$bbextended = array(
			"/\[list=1\](.*?)\[\/list\]/si" => "<ol>$1</ol>",
			"/\[list\](.*?)\[\/list\]/si" => "<ul>$1</ul>",
			"/\[url](.*?)\[\/url]/si" => "<a href=\"http://$1\" title=\"$1\">$1</a>",
			"/\[url=(.*?)\](.*?)\[\/url\]/si" => "<a href=\"$1\" title=\"$1\">$2</a>",
			"/\[img\]([^[]*)\[\/img\]/si" => "<img src=\"$1\" alt=\" \" class=\"img-responsive\" />",
		);
		foreach($bbextended as $match=>$replacement){
			$bbtext = preg_replace($match, $replacement, $bbtext);
		}
		$bbtext = str_replace('\n', "<br>", $bbtext);
		return $bbtext;
	}
}

?>