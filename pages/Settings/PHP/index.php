<?php
ob_start();
phpinfo();
$phpinfo = ob_get_contents();
ob_end_clean();
$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
print_r(getenv());
echo "
     <style type='text/css'>
         #phpinfo {}
         #phpinfo pre {margin: 0; font-family: monospace;}
         #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
         #phpinfo a:hover {text-decoration: underline;}
         #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
         #phpinfo .center {text-align: center;}
         #phpinfo .center table {margin: 1em auto; text-align: left;}
         #phpinfo .center th {text-align: center !important;}
         #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
         #phpinfo h1 {font-size: 150%;}
         #phpinfo h2 {font-size: 125%;}
         #phpinfo .p {text-align: left;}
         #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
         #phpinfo .h {background-color: #99c; font-weight: bold;}
         #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
         #phpinfo .v i {color: #999;}
         #phpinfo img {float: right; border: 0;}
         #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
     </style>
     <div id='phpinfo'>
         $phpinfo
     </div>
     ";
?>
<script>
    // Add event listener to the logout button
    document.addEventListener('DOMContentLoaded', function() {


        // Check if the theme is set to dark
        const isDarkTheme = localStorage.getItem('coreui-free-bootstrap-admin-template-theme') === 'dark';
        if (isDarkTheme) {
            document.getElementById('phpinfo').style = 'color: black;';
            const h2Elements = document.querySelectorAll('#phpinfo h2');
            h2Elements.forEach(h2 => {
                h2.style.color = 'white';
            });
            const h1Elements = document.querySelectorAll('#phpinfo h1');
            h1Elements.forEach(h1 => {
                h1.style.color = 'white';
            });
        }

    });
</script>