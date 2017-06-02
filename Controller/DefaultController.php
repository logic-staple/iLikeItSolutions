<?php

namespace iLikeItSolutions\GoogleAnalyticsApiBundle\Controller;

use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/analytic", name="homepage")
     * @Template()
     */
    //public function indexAction($viewId)
    public function indexAction()
    {

    	$session = $this->get('session');
    	if($session->has('access_token')){

	    	$viewId = "106341934";

	        $analyticsService = $this->get('google_analytics_api.api');

	        $client = $analyticsService->getClient();
	        $client->setAccessToken($session->get('access_token'));

	        $analytics = $analyticsService->getAnalytics();

	        // Create the DateRange object
	        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
	        $dateRange->setStartDate("30daysAgo");
	        $dateRange->setEndDate("today");

	        // Create the Metrics object
	        $sessions = new Google_Service_AnalyticsReporting_Metric();
	        $sessions->setExpression("ga:sessions");
	        $sessions->setAlias("sessions");

	        // Create the ReportRequest object
	        $request = new Google_Service_AnalyticsReporting_ReportRequest();
	        $request->setViewId($viewId);
	        $request->setDateRanges($dateRange);
	        $request->setMetrics([$sessions]);

	        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
	        $body->setReportRequests([$request]);
	        $report = $analytics->reports->batchGet($body);

	        // above code included into this helper method :

	        $sessions = $analyticsService->getSessionsDateRange($viewId,'30daysAgo','today');
	        $bounceRate = $analyticsService->getBounceRateDateRange($viewId,'30daysAgo','today');
	        $avgTimeOnPage = $analyticsService->getAvgTimeOnPageDateRange($viewId,'30daysAgo','today');
	        $pageViewsPerSession = $analyticsService->getPageviewsPerSessionDateRange($viewId,'30daysAgo','today');
	        $percentNewVisits = $analyticsService->getPercentNewVisitsDateRange($viewId,'30daysAgo','today');
	        $pageViews = $analyticsService->getPageViewsDateRange($viewId,'30daysAgo','today');
	        $avgPageLoadTime = $analyticsService->getAvgPageLoadTimeDateRange($viewId,'30daysAgo','today');
	        //echo "<pre>"; print_r($bounceRate); die;
	        //return $this->render('AnalyticBundle:Default:index.html.twig');
	        return $this->render('AnalyticBundle:Default:index.html.twig', array(
	            'analytics'     =>  $analytics,
	            'client'        =>  $client,
	            'report'        =>  $report,
	            'data'          =>  [
	                'sessions'              =>  $sessions,
	                'bounce_rate'           =>  $bounceRate,
	                'avg_time_on_page'      =>  $avgTimeOnPage,
	                'page_view_per_session' =>  $pageViewsPerSession,
	                'percent_new_visits'    =>  $percentNewVisits,
	                'page_views'            =>  $pageViews,
	                'avg_page_load_time'    =>  $avgPageLoadTime
	            ]
	        ));
		}
		else 
		{
			//$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/callback';
			return $this->redirectToRoute('google_analytics_api_callback', array(), 301);
		}
    }

    /**
     * Matches /callback exactly
     *
     * @Route("/callback", name="google_analytics_api_callback")
     */
    public function callbackAction()
    {
    	$analyticsService = $this->get('google_analytics_api.api');
    	$client = $analyticsService->getClient();

        // Handle authorization flow from the server.
		if (! isset($_GET['code']))
		{
			$auth_url = $client->createAuthUrl();
			$auth_url = filter_var($auth_url, FILTER_SANITIZE_URL);
			return $this->redirect($auth_url);
		} 
		else
		{
			$client->authenticate($_GET['code']);
			$session = $this->get('session');
			if (isset($_GET['code']))
			{
				$session->set('access_token', $client->getAccessToken());
			}
			$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/analytic';
			$redirect_uri =   filter_var($redirect_uri, FILTER_SANITIZE_URL);
			return $this->redirect($redirect_uri);
			//return $this->redirectToRoute('analytic_homepage', array(), 301);
		}
    }
}

