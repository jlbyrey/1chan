				<div class="l-static-wrap">
					<div class="b-static m-justify">
                        <h1>Форсометр<sup>beta</sup></h1>
                        <form action="" method="get">
                            <div>
                                <input type="text" name="query" value="<?php if(isset($query)) echo htmlspecialchars($query); ?>" style="width:460px" />
                                <input type="submit" value="Вывести график" />
                            </div>
                        </form>
                        <p><em>Форсометр</em> &mdash; это инновационный инструмент для отделения зерен от плевел, мух от котлет, мемов от форсов. Он работает на основе полного поискового индекса и строит график по трем параметрам: <strong>общее число уникальных постеров</strong>, <strong>число постов</strong> по поисковому запросу и <strong>число уникальных постеров в месяц</strong> по поисковому запросу. Вам же остается только сделать выводы на основе графика.</p>
                    </div>
                <?php if (isset($not_found)): ?>
					<div class="b-static m-justify">
                        <h3>Не найдено ни одного вхождения поисковой строки.</h3>                    
                    </div>
                <?php elseif (isset($results)): ?>
                    <div class="js-chart" id="result_chart" style="width: 660px; height: 500px; margin: 0 auto; font-size: 12px;"></div>

                    <script type="text/javascript">
                        var data = <?php echo($results); ?>;
                    </script>

                    <!-- Graph library: -->

                    <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="/js/excanvas.js"></script><![endif]-->
                    <script language="javascript" type="text/javascript" src="/js/jquery.jqplot.min.js"></script>
                    <script type="text/javascript" src="/js/plugins/jqplot.barRenderer.min.js"></script>
                    <script type="text/javascript" src="/js/plugins/jqplot.highlighter.min.js"></script>
                    <script type="text/javascript" src="/js/plugins/jqplot.cursor.min.js"></script>
                    <script type="text/javascript" src="/js/plugins/jqplot.pointLabels.min.js"></script>
                    <script type="text/javascript" src="/js/plugins/jqplot.meterGaugeRenderer.min.js"></script>
                    <script type="text/javascript" src="/js/plugins/jqplot.dateAxisRenderer.min.js"></script>
                    <link rel="stylesheet" type="text/css" href="/css/jquery.jqplot.min.css" />

                    <script type="text/javascript">
                        $(document).ready(function () {
                            var line1 = [], line2 = [], line3 = [], line4 = [];

                            for (var i in data["posts"])
                            if (data["posts"].hasOwnProperty(i)) {
                                line1.push([i, data["posts"][i]]);
                            }

                            for (var i in data["posters"])
                            if (data["posters"].hasOwnProperty(i)) {
                                line2.push([i, data["posters"][i]]);
                            }

                            for (var i in data["uniq_m"])
                            if (data["uniq_m"].hasOwnProperty(i)) {
                                line3.push([i, data["uniq_m"][i]]);
                            }

                            for (var i in data["uniq_f"])
                            if (data["uniq_f"].hasOwnProperty(i)) {
                                line4.push([i, data["uniq_f"][i]]);
                            }

                            $.jqplot('result_chart', [line2, line1, line3], {
                                animate: true,
                                animateReplot: true,
                                cursor: {
                                    show: true,
                                    zoom: true,
                                    looseZoom: true,
                                    showTooltip: true
                                },
                                title:'Статистика постов с «<?php echo(htmlspecialchars($query)); ?>»:',
                                axes:{
                                    xaxis:{
                                        renderer:$.jqplot.DateAxisRenderer,
                                        tickOptions:{formatString:'%Y/%m'}
                                    }
                                },
                                series:[
                                    {fill:true, pointLabels: { show:true }, color: '#eee', 'label': 'Общее число постеров'},
                                    {fill:true, color: '#aaa', 'label': 'Найденные посты (по запросу)'},
                                    {lineWidth:3, markerOptions:{style:'square'}, pointLabels: { show:true }, color: 'red', 'label': 'Постеров (по запросу)'}
                                ],
                                highlighter: {
                                            show: true, 
                                            showLabel: true, 
                                            tooltipAxes: 'y',
                                            sizeAdjust: 7.5 , tooltipLocation : 'ne'
                                        },
                                legend: { show:true, location: 'nw' }
                            });
                        });
                    </script>

                <?php endif; ?>
                </div>
