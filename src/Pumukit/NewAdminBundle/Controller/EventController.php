<?php

namespace Pumukit\NewAdminBundle\Controller;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventController extends AdminController
{
    /**
     * @var array
     */
    static $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    /**
     * Overwrite to get the calendar
     * @Template
     */
    public function indexAction(Request $request)
    {
        $config = $this->getConfiguration();

        $criteria = $this->getCriteria($config);
        $resources = $this->getResources($request, $config, $criteria);

        $update_session = true;
        foreach($resources['events'] as $event) {
            if($event->getId() == $this->get('session')->get('admin/event/id')){
                $update_session = false;
            }
        }
 
        if($update_session){
            $this->get('session')->remove('admin/event/id');
        }

        return $resources;
    }

    /**
     * List action
     * @Template
     */
    public function listAction(Request $request)
    {
        $config = $this->getConfiguration();

        $sorting = $request->get('sorting');

        $criteria = $this->getCriteria($config);
        $resources = $this->getResources($request, $config, $criteria);

        return $resources;
    }

    /**
     * Get calendar
     */
    private function getCalendar($config, $request)
    {
        /*if (!$this->getUser()->hasAttribute('page', 'tv_admin/event'))
          $this->getUser()->setAttribute('page', 1, 'tv_admin/event');*/

        if (!$this->get('session')->get('admin/event/month')) {
            $this->get('session')->set('admin/event/month', date('m'));
        }
        if (!$this->get('session')->get('admin/event/year')) {
            $this->get('session')->set('admin/event/year', date('Y'));
        }

        $m = $this->get('session')->get('admin/event/month');
        $y = $this->get('session')->get('admin/event/year');

        if ($request->query->get('month') == "next") {
            $changed_date = mktime(0, 0, 0, $m+1, 1, $y);
            $this->get('session')->set('admin/event/year', date("Y", $changed_date));
            $this->get('session')->set('admin/event/month', date("m", $changed_date));
        } elseif ($request->query->get('month') == "previous") {
            $changed_date = mktime(0, 0, 0, $m-1, 1, $y);
            $this->get('session')->set('admin/event/year', date("Y", $changed_date));
            $this->get('session')->set('admin/event/month', date("m", $changed_date));
        } elseif ($request->query->get('month') == "today") {
            $this->get('session')->set('admin/event/year', date("Y"));
            $this->get('session')->set('admin/event/month', date("m"));
        }

        $m = $this->get('session')->get('admin/event/month', date('m'));
        $y = $this->get('session')->get('admin/event/year', date('Y'));

        $calendar = $this->generateArray($m, $y);

        return (array($m, $y, $calendar));
    }

    /**
     * Get days in month
     */
    private static function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12) {
            return 0;
        }

        $d = self::$daysInMonth[$month - 1];

        if ($month == 2) {
            if ($year%4 == 0) {
                if ($year%100 == 0) {
                    if ($year%400 == 0) {
                        $d = 29;
                    }
                } else {
                    $d = 29;
                }
            }
        }

        return $d;
    }

    /**
     * Generate array
     */
    private static function generateArray($month, $year)
    {
        $aux = array();

        $dweek = date('N', mktime(0, 0, 0, $month, 1, $year)) - 1;
        foreach (range(1, self::getDaysInMonth($month, $year)) as $i) {
            $aux[intval($dweek / 7)][($dweek % 7)] = $i;
            $dweek++;
        }

        return $aux;
    }

    /**
     * Gets the criteria values
     */
    public function getCriteria($config)
    {
        $criteria = $config->getCriteria();

        if (array_key_exists('reset', $criteria)) {
            $this->get('session')->remove('admin/event/criteria');
        } elseif ($criteria) {
            $this->get('session')->set('admin/event/criteria', $criteria);
        }
        $criteria = $this->get('session')->get('admin/event/criteria', array());

        $new_criteria = array();
        foreach ($criteria as $property => $value) {
            //preg_match('/^\/.*?\/[imxlsu]*$/i', $e)
            if (('' !== $value) && ('date' !== $property)) {
                $new_criteria[$property] = new \MongoRegex('/'.$value.'/i');
            } elseif (('' !== $value) && ('date' == $property)) {
                $date_from = new \DateTime($value['from']);
                $date_to = new \DateTime($value['to']);
                $new_criteria[$property] = array('$gte' => $date_from, '$lt' => $date_to);
            }
        }

        return $new_criteria;
    }

    /**
     * Gets the list of resources according to a criteria
     */
    public function getResources(Request $request, $config, $criteria)
    {
        $sorting = $config->getSorting();
        $repository = $this->getRepository();
        $session = $this->get('session');
        $session_namespace = 'admin/event';

        $m = '';
        $y = '';
        $calendar = array();


        if ($config->isPaginated()) {
            $resources = $this
                ->resourceResolver
                ->getResource($repository, 'createPaginator', array($criteria, $sorting));

            if ($request->get('page', null)) {
                $session->set($session_namespace.'/page', $request->get('page', 1));
            }

            // ADDED FROM ADMIN CONTROLLER
            if ($request->get('paginate', null)) {
                $session->set($session_namespace.'/paginate', $request->get('paginate', 10));
            }

            $resources
                ->setCurrentPage($session->get($session_namespace.'/page', 1), true, true)
                ->setMaxPerPage($config->getPaginationMaxPerPage());
        } else {
            $resources = $this
                ->resourceResolver
                ->getResource($repository, 'findBy', array($criteria, $sorting, $config->getLimit()));
        }

        if ("cal" == $request->query->get('cal')) {
            $session->set($session_namespace.'/cal', 'cal');
        } elseif ("list" == $request->query->get('cal')) {
            $session->remove($session_namespace.'/cal');
        }


        /* TODO DELETE IF IT WORKS THE below
        if ($session->has($session_namespace.'/cal')) {
            list($m, $y, $calendar) = $this->getCalendar($config, $request);
            $resources = array('events' => $resources, 'm' => $m, 'y' => $y, 'calendar' => $calendar);
        } else {
            //$resources = array('events' => $resources); // TODO TEST
            $resources = array('events' => $resources, 'm' => '', 'y' => '', 'calendar' => array());
        }
        */

        list($m, $y, $calendar) = $this->getCalendar($config, $request);
        $resources = array('events' => $resources, 'm' => $m, 'y' => $y, 'calendar' => $calendar);


        return $resources;
    }
}