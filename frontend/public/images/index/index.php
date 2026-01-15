<?
/*[WoA-IT] ExpLo1T ICQ 321769*/
require_once "func/connect.php";
db_open();

$current_page = explode ('?',$QUERY_STRING);
$str= $current_page[0];
if($str!=0 or $str!=''){
if(empty($ip)){if (getenv('HTTP_X_FORWARDED_FOR')){$ip=getenv('HTTP_X_FORWARDED_FOR');}else{$ip=getenv('REMOTE_ADDR');}}
$count= mysqli_num_rows(mysqli_query($db_link, "SELECT * FROM referal WHERE ip = '$ip'"));
if($count==0){mysqli_query($db_link, "INSERT INTO referal (uid,ip) VALUES ('$str','$ip')"); mysqli_query($db_link, "UPDATE user SET nv=nv+0.5 WHERE id='$str' LIMIT 1;");}}
?>
<HTML>
<HEAD>
<TITLE>Лучшая Онлайн Игра - Земли, которых нет... - Главная</TITLE>
<LINK href="/css/index.css" rel=stylesheet type=text/css>
<META Http-Equiv="Content-Type" Content="text/html; charset=windows-1251">
<META Http-Equiv="Cache-Control" Content="No-Cache">
<META Http-Equiv="Pragma" Content="No-Cache">
<META Http-Equiv="Expires" Content="0">
<META name="description" content="Фэнтези онлайн игра с элементами стратегии, квестовой частью и возможностью самим участвовать в создании нового мира в Землях, которых нет...">
<META name="keywords" content="игра, играть, рпг, онлайн, online, fantasy, фэнтези, квест, алхимия, мир, земли, стратегия, магия, стихия, арена, бои, клан, семья, братство, сражение, тьма, свет, хаос, сумерки, удар, меч, нож, топор, дубина, щит, броня, доспех, шлем, перчатки, амулет, кулон, кольцо, пояс, зелья, карта, замки, шахты, лавка, таверна, артефакты, раритеты, свитки, свиток, школа, од, рыцарь, маг, друид, гоблин, орк, призрак, эльф, отдых, развлечение, чат, общение, знакомства, форум, власть, золото, серебро, телепорт, банк, рынок, мастерская, тактика, больница, храм, бог, демон, защита, сила, удача, ловкость, война, орден, аптека, почта, реторта, ступка, пестик, дистиллятор, nv, nl, нв, нл, невер">
<SCRIPT src="/js/index_main.js"></SCRIPT>
<SCRIPT src="/js/top.js"></SCRIPT>
</HEAD>
<BODY>
<?php if(!isset($_GET['error'])){$_GET['error']='';}?>
global $db_link;
<SCRIPT language="JavaScript">
var error = "<?=$_GET['error']?>";
index();
</SCRIPT>

</BODY>
</HTML>