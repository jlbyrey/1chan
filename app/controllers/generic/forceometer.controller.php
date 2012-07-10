<?php
/**
 * Контроллер форсометра:
 */
class Generic_ForceometerController extends BaseController
{
	public function indexAction(Application $application, Template $template)
	{
        $template -> setParameter('title', 'Форс-о-метр');

        if (array_key_exists('query', $_GET)) {
            $query = $this['query'] = $_GET['query'];
            
            $search = new SphinxClient();
       	    $search -> SetServer('localhost', 3312);

            $search -> SetGroupBy('created_at', SPH_GROUPBY_MONTH);

            // Полный поиск (запрос):
		    $search -> ResetFilters();
            $search -> SetMatchMode(SPH_MATCH_PHRASE);
		    $search -> AddQuery($query, 'forceometer');
            
            // Поиск уникальных в месяц (запрос):
		    $search -> ResetFilters();
            $search -> SetMatchMode(SPH_MATCH_PHRASE);
            $search -> SetFilter('uniq_m', array(1));
		    $search -> AddQuery($query, 'forceometer');

            // Поиск уникальных вообще (запрос):
		    $search -> ResetFilters();
            $search -> SetMatchMode(SPH_MATCH_PHRASE);
            $search -> SetFilter('uniq_f', array(1));
		    $search -> AddQuery($query, 'forceometer');

		    $query_result = $search -> RunQueries();

            // Поиск уникальных постеров месяца (вообще):
		    $search -> ResetFilters();
            $search -> SetMatchMode(SPH_MATCH_FULLSCAN);
            $search -> SetFilter('uniq_m', array(1));
            $bare_query = $search -> Query('', 'forceometer');
/*
		    $search -> ResetFilters();
            $search -> SetMatchMode(SPH_MATCH_FULLSCAN);
            $bare_query2 = $search -> Query('', 'forceometer');
*/
            if (!$query_result || $query_result[0]['total_found'] == 0) {
                $this['not_found'] = 1;
                return true;
            }

            $result = array(
                'posts'   => array(),
                'posters' => array(),
                'uniq_m'  => array(),
                'uniq_f'  => array()
            );

            for ($y = 2009; $y <= date('Y'); $y++) {
                for ($m = 1; $m <= 12; $m++) {
                    // Пропуск несуществующих дат и текущего месяца:
                    if ($y == 2009 && $m < 3) continue;
                    if ($y == date('Y') && $m >= date('m')) break;
                    
                    $stamp = $y .'-'. ($m < 10 ? '0'. $m : $m) .'-01';

                    $result['posts'][$stamp]   = 0;
                    $result['posters'][$stamp] = 0;
                    $result['uniq_m'][$stamp]  = 0;
                    $result['uniq_f'][$stamp]  = 0;
                }
            }

            // Проходим результаты:
            if ($query_result[0]['matches']) {
                foreach($query_result[0]['matches'] as $match) {
                    $date  = $match['attrs']['@groupby'];
                    $stamp = substr($date, 0, 4) .'-'. substr($date, 4, 2) .'-01';

                    if (array_key_exists($stamp, $result['posts']))
                        $result['posts'][$stamp] = $match['attrs']['@count'];
                }
            }

            if ($bare_query['matches']) {
                foreach($bare_query['matches'] as $match) {
                    $date = $match['attrs']['@groupby'];
                    $stamp = substr($date, 0, 4) .'-'. substr($date, 4, 2) .'-01';

                    if (array_key_exists($stamp, $result['posters']))
                        $result['posters'][$stamp] = $match['attrs']['@count'];
                }
            }

            if ($query_result[1]['matches']) {
                foreach($query_result[1]['matches'] as $match) {
                    $date = $match['attrs']['@groupby'];
                    $stamp = substr($date, 0, 4) .'-'. substr($date, 4, 2) .'-01';

                    if (array_key_exists($stamp, $result['uniq_m']))
                        $result['uniq_m'][$stamp] = $match['attrs']['@count'];
                }
            }
/*
            if ($bare_query2['matches']) {
                foreach($bare_query2['matches'] as $match) {
                    $date = $match['attrs']['@groupby'];
                    $stamp = substr($date, 0, 4) .'-'. substr($date, 4, 2) .'-01';

                    if (array_key_exists($stamp, $result['uniq_f']))
                        $result['uniq_f'][$stamp] = $match['attrs']['@count'];
                }
            }*/

            foreach($result['posts'] as $date => $count) {
                if ($count == 0) {
                    unset($result['posts'][$date]);
                    unset($result['posters'][$date]);
                    unset($result['uniq_f'][$date]);
                    unset($result['uniq_m'][$date]);
                }
            }

            $this['results'] = json_encode($result);
            unset($result);
            return true;
        }
        
		return true;
	}
}
