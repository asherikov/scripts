<!doctype html public "-//IETF//DTD HTML//EN">
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
   <title>dictionary</title>
</head>

<?
    $word = $_POST['tword'];
    $nbeg = $_POST['nbeg'];
    $nexact = $_POST['nexact'];
    $lang = $_POST['lang'];
    switch ($lang)
    {
        case 'english':    
            $dictionary = "./dictionaries/Mueller7accentGPL.koi" ;
            break;
        case 'german':    
            $dictionary = "./dictionaries/gerFREE.koi" ;
            break;
        default:    
            $dictionary = "./dictionaries/Mueller7accentGPL.koi" ;
            break;
    };
?>

<form action="<?echo $_SERVER['REQUEST_URI']?>" method="post">
    <input type="radio" name="lang" value="english" checked>english</input>
    <input type="radio" name="lang" value="german" <?echo $lang=='german'?"checked":""?>>german</input>
    <br>

    <input type="text" name="tword" size="40" value="<?echo $word?>"></input> 
    <input type="submit" value="Enter"></input><br>

    <input type="checkbox" name="nbeg" <? echo $nbeg?"checked":""?>>&nbsp;inside&nbsp;article</input>
    <input type="checkbox" name="nexact" <? echo $nexact?"checked":""?>>&nbsp;inside&nbsp;word</input>
    <br>
</form>
<hr>

<?

// USE IT FREE !!!

$wword = ereg_replace ( "[^\*a-zA-Zабвгдеёжзийклмнопрстуфхцчшщъыьэюя]", "" , $word ) ; // remove invalid char's
$wword = str_replace ( "*" , "[a-zA-Zабвгдеёжзийклмнопрстуфхцчшщъыьэюя]*" , $wword ) ;

$pref = $nexact ? "[-a-zабвгдеёжзийклмнопрстуфхцчшщъыьэюя]*" : "([^a-zабвгдеёжзийклмнопрстуфхцчшщъыьэюя]|^)" ;
$suff = $nexact ? "[-a-zабвгдеёжзийклмнопрстуфхцчшщъыьэюя]*" : "([^a-zабвгдеёжзийклмнопрстуфхцчшщъыьэюя]|$)" ;

if (!$nbeg)
{
    $pref = "^" . $pref;
    $suff = $suff . " ";
}

if ( $wword ) 
{
    exec("grep -E -h -i \"".$pref.$wword.$suff."\" ".$dictionary, $output);
    foreach ($output as $line)
    {
        $line_split = preg_split("/  /", $line);
        echo htmlentities($line_split[0])."&nbsp;&nbsp;&nbsp;";
        
        $variants = preg_split("/_[IVX]* /", $line_split[1], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($variants as $variant)
        {
            preg_match("/\[[^[]*\]/", $variant, $matches);  

            $tbl = array (
                // IPA        Unicode
                "Q" => "&#230;", // "a" from "man"
                "W" => "&#695;", // "w"
                "A" => "&#593;", // "a" from "past"
                Chr(249)      => ":",      // ":"
                Chr(171)      => "&#601;", // "e" from "her"
                "E" => "&#603;", // "e" first from diphthong in "care"
                Chr(141)      => "&#596;", // "o" from "wash"
                Chr(195)      => "&#652;", // "a" from "son"
                "I" => "&#618;", // "i" from "ink"
                Chr(200)      => "&#712;", // "'"
                Chr(199)      => "&#716;", // ","
                "H" => "&#688;", // "h"
                "Z" => "&#658;", // "z"
                "N" => "&#331;", // "ng"
                "S" => "&#643;", // "sh"
                "D" => "&#240;", // "th" with voice
                "T" => "&#952;", // "th"
            );
            echo "<br />&nbsp;&nbsp;".strtr($matches[0], $tbl);


            // translation
            $translation = preg_replace ("/\[[^[]*\]/", "", $variant);
            $pattern = array (
                "/( _[a-z]*\. )/", 
                "/ [1-9]\. /", 
                "/( [1-9][0-9]*)> /",
                "/( [абвгдеёжзийклмнопрстуфхцчшщъыьэюя])> /",
            );
            $replace = array (
                " <br />&nbsp;&nbsp;&nbsp; $1", 
                "",
                " <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$1) ",
                " <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$1) ",
            );
            
            if ($nbeg)
            {
                $pattern[] = "/(".$wword.")/i";
                $replace[] = "<font color=red>$1</font>";
            }
            echo preg_replace($pattern, $replace, $translation);
        }
        echo "<br/><br/>";
    } 
}
else
{ 
    echo "Здесь будет результат.<br> 
      Словарь англо-русский, поэтому поиск с начала статьи - перевод
      с английского. Поиск внутри статьи работает как для английских так и
      для русских слов.<br>
      Внутри слова символ '*' заменяет любую последовательность знаков,
      т.е. 'крок*л' найдёт и 'крокодил' и 'микроклимат'.";
}

echo "<hr>" ;
?>

</body>
</html>
