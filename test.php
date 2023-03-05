<?php

require_once __DIR__ . '/vendor/autoload.php';

$tpl = new App\Template(__DIR__ . '/templates');

?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="/templates/css/reset.css" />

        <style>
            @font-face {
              font-family: "Inter";
              src: url("/fonts/Inter-V.ttf");
            }

            * {
              font-family: "Inter";
            }

            body {
                background: #F5F5F5;
                margin: 0;
                padding: 0;
            }

            #test--chassis {
                background: #fff;
                height: 714px;
                padding: 30px 30px 0 29px;
                position: relative;
                width: 470px;
            }

            #test--chassis--toggle {
                background: #090;
                cursor: pointer;
                height: 30px;
                position: absolute;
                right: 0;
                top: 0;
                width: 30px;
            }

            #test--chassis--toggle-2 {
                background: #009;
                cursor: pointer;
                height: 30px;
                position: absolute;
                right: 30px;
                top: 0;
                width: 30px;
            }

            #test--chassis--reference {
                background: transparent url(/test/lead-card.png) no-repeat left top;
                height: 100%;
                left: 0;
                opacity: 50%;
                position: absolute;
                top: 0;
                width: 100%;
                z-index: 1000;
            }
        </style>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="/templates/script/jquery.maskedinput.min.js"></script>
        <script src="/templates/script/basic.js"></script>
        <script>let LEAD_STATUSES = <?= json_encode(\Model\Lead::STATUSES) ?></script>

        <script>
            (function($) {
                $(document).ready(function() {
                    $('#test--chassis--toggle').on('click', function(event) {
                        $('#test--chassis--reference').toggle();
                    });
                    $('#test--chassis--toggle-2').on('click', function(event) {
                        $('.lead-card').toggleClass('edit');
                    });
                });
            })(jQuery);
        </script>
    </head>
    <body>
        <div id="test--chassis--toggle"></div>
        <div id="test--chassis">
            <div id="test--chassis--reference" style="display: none;"></div>
            <?= $tpl->render('/components/lead-card') ?>
            <script>
                $(document).ready(event => {
                    const leadCard = $('.lead-card').leadCard();

                    $.post('/ajax/admin/leads.php', {action: 'get-lead', id: 1239}, response => {
                        leadCard.leadCard('fill', response.lead);
                    }, 'json');
                    //$('.lead-card').leadCard().leadCard('fill', {
                        //id: 123,
                        //status: 'new',
                        //created_at: '01.02.2022 11:22',
                        //inn: '123456789012',
                        //inn_added_at: '02.03.2022 22:33',
                        //name: 'Гриша',
                        //company_name: 'ООО Гришки',
                        //city: 'СПб',
                        //comment: 'Что-то там про лида',
                    //});
                });
            </script>
        </div>
    </body>
</html>
