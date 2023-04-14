<noscript>
<meta http-equiv='Refresh' content='0; URL=<?php print "$CFG->rwww/"; ?>validaAcesso.php?val=js'>
</noscript>

<script type="text/javascript">
    //verifica se o navegador é Internet Explorer versão 9 ou anterior
    var browser = navigator.appName;
    var ver = navigator.appVersion;
    var thestart = parseFloat(ver.indexOf("MSIE")) + 1;
    var brow_ver = parseFloat(ver.substring(thestart + 4, thestart + 7));
    if ((browser == "Microsoft Internet Explorer") && (brow_ver <= 9))
    {
        // redirecionando...
        window.location = "<?php print $CFG->rwww ?>/validaAcesso.php?val=nav";
    }
</script>

<script type="text/javascript">
    // Cookie handling 
    var Cookie =
            {
                read: function (name)
                {
                    var arrCookies = document.cookie.split('; ');
                    for (var i = 0; i < arrCookies.length; i++)
                    {
                        var arrCookie = arrCookies[i].split('=');
                        if (arrCookie[0] == name)
                        {
                            return decodeURIComponent(arrCookie[1]);
                        }
                    }
                    return false;
                },
                write: function (name, value, expires, path)
                {
                    if (expires)
                    {
                        var date = new Date();
                        date.setTime(date.getTime() + (((((expires * 24) * 60) * 60) * 1000)));
                        expires = '; expires=' + date.toGMTString();
                    }
                    else
                        expires = '';
                    if (!path)
                        path = '/';
                    document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=' + path;
                },
                remove: function (name)
                {
                    this.write(name, '', -1);
                }
            }

    // Detects if can set a cookie in the browser
    function browserSupportsCookies()
    {
        Cookie.write('cookiesEnabled', 1);
        var boolCookiesEnabled = Cookie.read('cookiesEnabled');
        Cookie.remove('cookiesEnabled');
        if (boolCookiesEnabled != 1)
        {
            return false;
        }
        return true;
    }

    if (!browserSupportsCookies())
    {
        // redirecionando...
        window.location = "<?php print $CFG->rwww ?>/validaAcesso.php?val=cok";
    }
</script>

<script type="text/javascript">
    // Detects if the browser supports Ajax 
    function browserSupportsAjax()
    {
        if (typeof XMLHttpRequest == "undefined" && typeof ActiveXObject == "undefined" && window.createRequest == "undefined")
        {
            return false;
        }
        return true;
    }

    if (!browserSupportsAjax())
    {
        // redirecionando...
        window.location = "<?php print $CFG->rwww ?>/validaAcesso.php?val=ajax";
    }
</script>