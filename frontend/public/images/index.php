<?
session_start();
include("./func/connect.php")
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" id="html">
<head>
<title>Анти-NeverLands: Земли, которых нет... - бесплатная браузерная онлайн игра в стиле фэнтези | Online игра (MMORPG - ролевые игры онлайн)</title>
<META http-equiv="Content-Type" content="text/html; charset=windows-1251">
<META name="keywords" content="браузерная онлайн игра, бесплатные онлайн игры, лучшие онлайн игры, online, game, рпг, рпг игры, скачать игры, mmorpg, mmorpg игра, онлайн рпг, rpg, rpg игры, игры онлайн, офис игры, ролевые онлайн игры, игры бесплатно, бесплатная игра, ММОРПГ, стратегия, фентези, fantasy, online игры, online игра, играть, игры, интернет игры, компьютерные игры, онлайн игры">
<META name="description" content="Официальный сайт бесплатной ролевой онлайн игры «Анти-NeverLands: Земли, которых нет». Огромный фэнтезийный мир">
<link href="./css/nl_index.css" rel="stylesheet" type="text/css">
<!--[if lt IE 7]>
<link href="./css/iepng.css" rel="stylesheet" type="text/css">
<![endif]-->
<SCRIPT src="./js/nl_main.js"></SCRIPT>
<SCRIPT src="./js/nl_index.js"></SCRIPT>
<SCRIPT src="./js/ajax.js"></SCRIPT>
<SCRIPT src="./js/t_v01.js"></SCRIPT>
</head>
<body id="body" onResize="resizer()">

    <script type="text/javascript">
    
    var h = Math.ceil(d.body.offsetHeight / 2) - 410;
    var h2 = get_doc_height();
    d.write('<style type="text/css">');

    if(h > 0)
    {
        d.write('#main{top:'+h+'px}\r\n');
        d.write('html{background-position:0px '+h+'px}\r\n');
        d.write('#wbg{height:'+h+'px}\r\n');
        d.write('body{background-position:50% '+ (h + 431) +'px}\r\n');
    }
    
    d.write('</style>');
    
    </script>

    <div id="wbg" style="position:absolute; top:0; width:100%; background-color:#fff;"></div>
    <div id="darker"></div>
    <table id="reg_warn" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td class="reg_warn_left">
                <div id="reg_warn_left_id" class="ok"></div>
            </td>
            <td class="reg_warn_mid">
            	<a href="javascript:close_obj('reg_warn', false);"><img src="images/index/blank.gif" height="30" width="151" alt=""/></a>
                <div id="reg_warn_mid_id" class="ok"></div>
                <span id="reg_warn_mess"></span>
            </td>
            <td class="reg_warn_right"></td>
        </tr>
    </table>
    <table id="pass_recovery" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td class="pass_left"></td>
            <td class="pass_mid">
                <a href="javascript:close_obj('pass_recovery', true);" class="pass_cl"></a>
                <div class="fields">
                    <div class="reg_field reg_input">
                        <div class="reg_field_left"><div class="reg_nick_label"></div></div>
                        <div class="reg_field_center"><input type="text" id="f_login" onchange="InCh(5);"></div>
                        <div class="reg_field_right" id="Ch5"></div>
                    </div>
                    <div class="reg_field reg_input">
                        <div class="reg_field_left"><div class="reg_email_label"></div></div>
                        <div class="reg_field_center"><input type="text" id="f_email" onchange="InCh(6);"></div>
                        <div class="reg_field_right" id="Ch6"></div>
                    </div>
                </div>
                <div class="text">
                    <a href="javascript: pass_recovery('bafb093282723bd7c1e0040f168afeef');" class="pass_button continue" id="recovery_next"></a>
                    <div style="clear:both;position:absolute; top:0; height:1px; width:1px;"></div>
                    <div class="shit"><div class="info" id="recovery_info"></div></div>
                    <p id="recovery_msg"><br>Для восстановления пароля необходимо ввести Ваш логин и e-mail, который был указан при регистрации.</p>
                </div>
            </td>
            <td class="pass_right"></td>
        </tr>
    </table>
    
    <table id="reg_form" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td class="reg_left"></td>
            <td class="reg_mid">
                <div class="reg_fields">
                    <div class="reg_field reg_input">
                        <div class="reg_field_left"><div class="reg_nick_label"></div></div>
                        <div class="reg_field_center"><input type="text" id="login" onchange="InCh(0);"></div>
                        <div class="reg_field_right" id="Ch0"></div>
                    </div>
                    <div class="reg_field reg_input">
                        <div class="reg_field_left"><div class="reg_email_label"></div></div>
                        <div class="reg_field_center"><input type="text" id="email" onchange="InCh(1);"></div>
                        <div class="reg_field_right" id="Ch1"></div>
                    </div>
                    <div class="reg_field reg_input">
                        <div class="reg_field_left"><div class="reg_pass_label"></div></div>
                        <div class="reg_field_center"><input type="password" id="pass" onchange="InCh(2);"></div>
                        <div class="reg_field_right" id="Ch2"></div>
                    </div>
                    <div class="reg_field reg_input">
                        <div class="reg_field_left"><div class="reg_more_label"></div></div>
                        <div class="reg_field_center"><input type="password" id="verify" onchange="InCh(3);"></div>
                        <div class="reg_field_right" id="Ch3"></div>
                    </div>
                    <div class="reg_field reg_sex">
                        <div class="reg_field_left"><div class="reg_sex_label"></div></div>
                        <div class="reg_field_center"><input type="radio" id="sex" name="sex" value="female" checked><input type="radio" id="sex" name="sex" value="male"></div>
                        <div class="reg_field_right"><div></div></div>
                    </div>
                    <div class="reg_field reg_captcha">
                        <div class="reg_field_left"><div class="reg_cap_label"></div></div>
                        <div class="reg_field_center" title="Нажмите чтоб обнавить код"><iframe name="check_refrech_auto" title="Нажмите чтоб обнавить код" valign="middle" src="check.php"height="45" border="0" width="145" FRAMESPACING=0 FRAMEBORDER=no BORDER=0 SCROLLING=no></iframe></div>
                        <div class="reg_field_right"></div>
                    </div>
                    <div class="reg_field reg_input">
                        <div class="reg_field_left"><div class="reg_code_label"></div></div>
                        <div class="reg_field_center"><input type="text" id="code" onchange="InCh(4);"></div>
                        <div class="reg_field_right" id="Ch4"></div>
                    </div>
                </div>
                <div class="reg_check">
                    <div id="reg_input_check">
                        <input id="condition" type="checkbox" checked="checked">
                        <label onclick="switch_agree(); return false;" for="agree"></label>
                    </div>
                    <div class="text">Принимаю условия пользовательского<br> соглашения</div>
                </div>
                <div class="reg_bot">
                    <a class="reg_done" href="javascript: RegSubmit('ba4a6b6c65ba49f97c0aeb88b8b03bc9');"></a>
                </div>
            </td>
            <td class="reg_right"><a class="reg_cl" href="javascript:close_obj('reg_form', true);"></a></td>
        </tr>
    </table>
    <table id="main" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td class="top_01">&nbsp;</td>
            <td width="155" class="top_02"><img src="images/index/all_02.jpg" alt=""/></td>
            <td width="195" class="top_03"><img src="images/index/all_03.jpg" alt=""/></td>
            <td width="189" class="top_04"><img src="images/index/all_04.jpg" alt=""/></td>
            <td width="213" class="top_05"><img src="images/index/all_05.jpg" alt=""/></td>
            <td class="top_02">&nbsp;</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="4">
                <div id="log_bg">
                    <div id="login_form">
                        <form method="post" id="auth_form" action="/game.php">
                            <input type="submit" style="height:0; width:0; overflow:hidden; position:absolute;">
                            <input onfocus="if(this.value == 'Логин') this.value  = '';" onblur="if(this.value == '') this.value = 'Логин';" type="text" name="player_nick" value="Логин">
                            <input id="temp" onfocus="this.style.display = 'none'; getElementById('real').style.display = 'inline'; getElementById('real').focus();" type="text" name="" value="Пароль">
                            <input style="display:none" id="real" onblur="if(this.value == '') {getElementById('temp').style.display = 'inline'; this.style.display = 'none';}" type="password" name="player_password" value="">
                        </form>
                        <a id="ent_button" href="javascript:document.getElementById('auth_form').submit();"></a>
                        <a id="forg_button" href="javascript:show_obj('pass_recovery', true);"></a>
                        <a id="reg_button" href="javascript:show_obj('reg_form', true);"></a>
                    </div>
                </div>
            </td>
            <td></td>
        </tr>
    </table>
    <div id="copyright">&copy; Copyright 2010, Анти-NeverLands.</div>
    <div id="flags">
        <div id="l_flag"><script type="text/javascript">d.write(top_small(1))</script></div>
        <div id="r_flag"><script type="text/javascript">d.write(top_small(2))</script></div>
    </div>
    
    <script type="text/javascript">
    </script>
</body>
</html>